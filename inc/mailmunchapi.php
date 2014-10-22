<?php
  class MailchimpMailmunchApi {
    protected $base_url = '';
    protected $email = '';
    protected $password = '';
    protected $headers = array('Accept' => 'application/json');
    protected $requestType = 'get';

    function __construct($email, $password, $url) {
      $this->email = $email;
      $this->password = $password;
      $this->base_url = $url;
    }

    function sites() {
      $this->requestType = 'get';
      return $this->ping('/sites');
    }

    function widgets($site_id, $widget_type_name) {
      $this->requestType = 'get';
      if (!empty($widget_type_name)) {
        return $this->ping('/sites/'.$site_id.'/widgets?widget_type_name='.$widget_type_name);
      } else {
        return $this->ping('/sites/'.$site_id.'/widgets');
      }
    }

    function getWidgetsHtml($site_id) {
      $this->requestType = 'get';
      return $this->ping('/sites/'.$site_id.'/widgets/wordpress?plugin=mailchimp');
    }

    function deleteWidget($site_id, $widget_id) {
      $this->requestType = 'post';
      return $this->ping('/sites/'.$site_id.'/widgets/'.$widget_id.'/delete');
    }

    function importWidgets($new_user_email, $new_user_password) {
      $this->requestType = 'post';
      return $this->ping('/wordpress/import_widgets', array(
        'external_id' => get_option("mc_mm_wordpress_instance_id"),
        'new_user_email' => $new_user_email,
        'new_user_password' => $new_user_password,
        'site_url' => home_url(),
        'site_name' => get_bloginfo()
      ));
    }

    function hasSite() {
      $request = $this->sites();
      $sites = $request['body'];
      $result = json_decode($sites);

      return (sizeof($result) > 0);
    }

    function createSite($sitename, $domain) {
      $this->requestType = 'post';
      return $this->ping('/sites', array(
        'site' => array(
          'name' => $sitename,
          'domain' => $domain,
          'wordpress' => true,
          'external_id' => get_option("mc_mm_wordpress_instance_id")
          )
      ));
    }

    function updateSite($sitename, $domain) {
      $this->requestType = 'post';
      return $this->ping('/wordpress/update_site', array(
        'external_id' => get_option("mc_mm_wordpress_instance_id"),
        'site' => array(
          'name' => $sitename,
          'domain' => $domain
          )
      ));
    }

    function createGuestUser() {
      $this->requestType = 'post';
      return $this->ping('/users', array(
        'user' => array(
          'email' => $this->email,
          'password' => $this->password,
          'guest_user' => true,
          'referral' => "mailchimp-wordpress-plugin"
          )
      ), true);
    }

    function signUp() {
      $this->requestType = 'post';
      return $this->ping('/users', array(
        'user' => array(
          'email' => $this->email,
          'password' => $this->password,
          'referral' => "mailchimp-wordpress-plugin"
          )
      ), true);
    }

    function updateGuest($new_email) {
      $this->requestType = 'post';
      return $this->ping('/wordpress/update_guest', array(
        'user' => array(
          'email' => $new_email,
          'guest_user' => false
          )
      ), true);
    }

    function signIn() {
      $this->requestType = 'post';
      return $this->ping('/users/sign_in');
    }

    function validPassword() {
     $this->requestType = 'get';
     $request = $this->ping('/sites');

     if ($request['response']['code'] == 200){
       return true;
     }
     else {
       return false;
     }
    }

    function isNewUser($email) {
      if (empty($email)) {
        $email = $this->email;
      }
      $this->requestType = 'get';
      $result = $this->ping('/users/exists?user[email]='. $email, array(), true);
      return $result['body'] == 'false';
    }

    function ping($path, $options=array(), $skipAuth=false) {
      $auth = array('auth' => array($this->email, $this->password));
      $type = $this->requestType;
      $url = $this->base_url. $path;
      $args = array(
        'headers' => array_merge($this->headers, array(
            'Authorization' => 'Basic ' . base64_encode( $this->email . ':' . $this->password )
          )
        ),
        'timeout' => 120
      );

      if ($type != 'post') {
        $request = wp_remote_get($url, $args);
      }
      else {
        $args = array_merge($args, array('method' => 'POST', 'body' => $options));
        $request = wp_remote_post($url, $args);
      }
      return $request;
    }
  }
?>