<?php
  /*
  Plugin Name: MailChimp Forms by MailMunch
  Plugin URI: http://connect.mailchimp.com/integrations/mailmunch-email-list-builder
  Description: The MailChimp plugin allows you to quickly and easily add signup forms for your MailChimp lists. Popup, Embedded, Top Bar and a variety of different options available.
  Version: 1.0.1
  Author: MailMunch
  Author URI: http://www.mailmunch.co
  License: GPL2
  */

  require_once( plugin_dir_path( __FILE__ ) . 'inc/mailmunchapi.php' );
  require_once( plugin_dir_path( __FILE__ ) . 'inc/common.php' );

  define( 'MAILCHIMP_MAILMUNCH_SLUG', "mailchimp-mailmunch");
  define( 'MAILCHIMP_MAILMUNCH_VER', "1.0.1");
  define( 'MAILCHIMP_MAILMUNCH_URL', "www.mailmunch.co");

  // Create unique WordPress instance ID
  if (get_option("mc_mm_wordpress_instance_id") == "") {
    update_option("mc_mm_wordpress_instance_id", uniqid());
  }

  // Adding Admin Menu
  add_action( 'admin_menu', 'mc_mm_register_page' );

  function mc_mm_register_page(){
     $menu_page = add_menu_page( 'MailChimp Settings', 'MailChimp', 'manage_options', MAILCHIMP_MAILMUNCH_SLUG, 'mc_mm_setup', plugins_url( 'img/icon.png', __FILE__ ), 103.786 ); 
     // If successful, load admin assets only on that page.
     if ($menu_page) add_action('load-' . $menu_page, 'mc_mm_load_plugin_assets');
  }

  function mc_mm_load_plugin_assets() {
    add_action( 'admin_enqueue_scripts', 'mc_mm_enqueue_admin_styles' );
    add_action( 'admin_enqueue_scripts', 'mc_mm_enqueue_admin_scripts'  );
  }

  function mc_mm_enqueue_admin_styles() {
    wp_enqueue_style(MAILCHIMP_MAILMUNCH_SLUG . '-admin-styles', plugins_url( 'css/admin.css', __FILE__ ), array(), MAILCHIMP_MAILMUNCH_VER );
  }

  function mc_mm_enqueue_admin_scripts() {
    wp_enqueue_script(MAILCHIMP_MAILMUNCH_SLUG . '-admin-script', plugins_url( 'js/admin.js', __FILE__ ), array( 'jquery' ), MAILCHIMP_MAILMUNCH_VER );
  }

  // Adding MailMunch Asset Files (JS + CSS) 
  function mc_mm_load_asset_code() {
    $mc_mm_data = unserialize(get_option("mc_mm_data"));
    if (!$mc_mm_data["script_src"]) return;

    if (is_single() || is_page()) {
      $post = get_post();
      $post_data = array("ID" => $post->ID, "post_name" => $post->post_name, "post_title" => $post->post_title, "post_type" => $post->post_type, "post_author" => $post->post_author, "post_status" => $post->post_status);
    }

    echo "<script type='text/javascript'>";
    echo "var _mmunch = {'front': false, 'page': false, 'post': false, 'category': false, 'author': false, 'search': false, 'attachment': false, 'tag': false};";
    if (is_front_page() || is_home()) { echo "_mmunch['front'] = true;"; }
    if (is_page()) { echo "_mmunch['page'] = true; _mmunch['pageData'] = ".json_encode($post_data).";"; }
    if (is_single()) { echo "_mmunch['post'] = true; _mmunch['postData'] = ".json_encode($post_data)."; _mmunch['postCategories'] = ".json_encode(get_the_category())."; _mmunch['postTags'] = ".json_encode(get_the_tags())."; _mmunch['postAuthor'] = ".json_encode(array("name" => get_the_author_meta("display_name"), "ID" => get_the_author_meta("ID"))).";"; }
    if (is_category()) { echo "_mmunch['category'] = true; _mmunch['categoryData'] = ".json_encode(get_category(get_query_var('cat'))).";"; }
    if (is_search()) { echo "_mmunch['search'] = true;"; }
    if (is_author()) { echo "_mmunch['author'] = true;"; }
    if (is_tag()) { echo "_mmunch['tag'] = true;"; }
    if (is_attachment()) { echo "_mmunch['attachment'] = true;"; }

    echo "(function(){ setTimeout(function(){ var d = document, f = d.getElementsByTagName('script')[0], s = d.createElement('script'); s.type = 'text/javascript'; s.async = true; s.src = '".$mc_mm_data["script_src"]."'; f.parentNode.insertBefore(s, f); }, 1); })();";
    echo "</script>";
  }

  add_action('init', 'mc_mm_assets');

  function mc_mm_assets() {
    $mc_mm_data = unserialize(get_option("mc_mm_data"));
    if (count($mc_mm_data) == 0) return;

    if (function_exists('wp_footer')) {
      if (!$_POST['mc_mm_data']) {
        add_action( 'wp_footer', 'mc_mm_load_asset_code' ); 
      }
    }
    elseif (function_exists('wp_head')) {
      if (!$_POST['mc_mm_data']) {
        add_action( 'wp_head', 'mc_mm_load_asset_code' ); 
      }
    }
  }

  function mc_mm_add_post_containers($content) {
    if (is_single() || is_page()) {
      $content = mc_mm_insert_form_after_paragraph("<div class='mailmunch-forms-in-post-middle' style='display: none !important;'></div>", "middle", $content);
      $content = "<div class='mailmunch-forms-before-post' style='display: none !important;'></div>" . $content . "<div class='mailmunch-forms-after-post' style='display: none !important;'></div>";
    }

    return $content;
  }

  function mc_mm_insert_form_after_paragraph($insertion, $paragraph_id, $content) {
    $closing_p = '</p>';
    $paragraphs = explode($closing_p, $content);
    if ($paragraph_id == "middle") {
      $paragraph_id = round(sizeof($paragraphs)/2);
    }

    foreach ($paragraphs as $index => $paragraph) {
      if (trim($paragraph)) {
        $paragraphs[$index] .= $closing_p;
      }

      if ($paragraph_id == $index + 1) {
        $paragraphs[$index] .= $insertion;
      }
    }
    return implode('', $paragraphs);
  }

  add_filter( 'the_content', 'mc_mm_add_post_containers' );

  function mc_mm_shortcode_form($atts) {
    return "<div class='mailmunch-forms-short-code mailmunch-forms-widget-".$atts['id']."' style='display: none !important;'></div>";
  }

  add_shortcode('mailmunch-form', 'mc_mm_shortcode_form');

  function mc_mm_setup() {
    $mm_helpers = new MailchimpMailmunchHelpers();
    $mc_mm_data = unserialize(get_option("mc_mm_data"));
    $mc_mm_data["site_url"] = home_url();
    $mc_mm_data["site_name"] = get_bloginfo();
    update_option("mc_mm_data", serialize($mc_mm_data));

    // This is a POST request. Let's save data first.
    if ($_POST) {
      $post_data = $_POST["mc_mm_data"];
      $post_action = $_POST["action"];

      if ($post_action == "save_settings") { 

        $mc_mm_data = array_merge(unserialize(get_option('mc_mm_data')), $post_data);
        update_option("mc_mm_data", serialize($mc_mm_data));
      
      } else if ($post_action == "sign_in") {

        $mm = new MailchimpMailmunchApi($_POST["email"], $_POST["password"], "http://".MAILCHIMP_MAILMUNCH_URL);
        if ($mm->validPassword()) {
          if (get_option("mc_mm_guest_user")) {
            // User exists and credentials are correct
            // Let's move optin forms from guest user to real user
            $account_info = $mm_helpers->getEmailPassword();
            $mc_mm_email = $account_info['email'];
            $mc_mm_password = $account_info['password'];
            $mm = new MailchimpMailmunchApi($mc_mm_email, $mc_mm_password, "http://".MAILCHIMP_MAILMUNCH_URL);
            $result = $mm->importWidgets($_POST["email"], $_POST["password"]);
          }

          update_option("mc_mm_user_email", $_POST["email"]);
          update_option("mc_mm_user_password", $_POST["password"]);
          delete_option("mc_mm_guest_user");
        }

      } else if ($post_action == "sign_up") {

        if (empty($_POST["email"])) {
          $invalid_email = true;
        } else {
          $account_info = $mm_helpers->getEmailPassword();
          $mc_mm_email = $account_info['email'];
          $mc_mm_password = $account_info['password'];

          $mm = new MailchimpMailmunchApi($mc_mm_email, $mc_mm_password, "http://".MAILCHIMP_MAILMUNCH_URL);
          if ($mm->isNewUser($_POST['email'])) {
            $update_result = $mm->updateGuest($_POST['email']);
            $result = json_decode($update_result['body']);
            update_option("mc_mm_user_email", $result->email);
            if (!$result->guest_user) { delete_option("mc_mm_guest_user"); }
            $mc_mm_email = $result->email;

            // We have update the guest with real email address, let's create a site now
            $mm = new MailchimpMailmunchApi($mc_mm_email, $mc_mm_password, "http://".MAILCHIMP_MAILMUNCH_URL);

            $update_result = $mm->updateSite($mc_mm_data["site_name"], $mc_mm_data["site_url"]);
            $result = json_decode($update_result['body']);
            $mc_mm_data = unserialize(get_option("mc_mm_data"));
            $mc_mm_data["site_url"] = $result->domain;
            $mc_mm_data["site_name"] = $result->name;
            update_option("mc_mm_data", serialize($mc_mm_data));
          } else {
            $user_exists = true;
          }
        }

      } else if ($post_action == "unlink_account") {

        $mc_mm_data = array();
        $mc_mm_data["site_url"] = home_url();
        $mc_mm_data["site_name"] = get_bloginfo();
        update_option("mc_mm_data", serialize($mc_mm_data));
        delete_option("mc_mm_user_email");
        delete_option("mc_mm_user_password");

      } else if ($post_action == "delete_widget") {

        if ($_POST["site_id"] && $_POST["widget_id"]) {
          $account_info = $mm_helpers->getEmailPassword();
          $mc_mm_email = $account_info['email'];
          $mc_mm_password = $account_info['password'];
          $mm = new MailchimpMailmunchApi($account_info['email'], $account_info["password"], "http://".MAILCHIMP_MAILMUNCH_URL);
          $request = $mm->deleteWidget($_POST["site_id"], $_POST["widget_id"]);
        }

      }
    }

    // If the user does not exists, create a GUEST user
    if (get_option("mc_mm_user_email") == "") {
      $mc_mm_email = "guest_".uniqid()."@mailmunch.co";
      $mc_mm_password = uniqid();
      $mm = new MailchimpMailmunchApi($mc_mm_email, $mc_mm_password, "http://".MAILCHIMP_MAILMUNCH_URL);
      $mm->createGuestUser();
      update_option("mc_mm_user_email", $mc_mm_email);
      update_option("mc_mm_user_password", $mc_mm_password);
      update_option("mc_mm_guest_user", true);
    }

    // If we already have the user's email stored, let's create the API instance
    // If we don't have it yet, make sure NOT to phone home any user data
    if (get_option("mc_mm_user_email") != "") {
      $account_info = $mm_helpers->getEmailPassword();
      $mc_mm_email = $account_info['email'];
      $mc_mm_password = $account_info['password'];

      $mm = new MailchimpMailmunchApi($mc_mm_email, $mc_mm_password, "http://".MAILCHIMP_MAILMUNCH_URL);
      if (!$mm->validPassword()) {
        // Invalid user, create a GUEST user
        $mc_mm_email = "guest_".uniqid()."@mailmunch.co";
        $mc_mm_password = uniqid();
        $mm = new MailchimpMailmunchApi($mc_mm_email, $mc_mm_password, "http://".MAILCHIMP_MAILMUNCH_URL);
        $mm->createGuestUser();
        update_option("mc_mm_user_email", $mc_mm_email);
        update_option("mc_mm_user_password", $mc_mm_password);
        update_option("mc_mm_guest_user", true);
      }
    }

    $mc_mm_guest_user = get_option("mc_mm_guest_user");


    if ($mc_mm_guest_user) {
      // This is a Guest USER. Do not collect any user data.
      $sites = $mm_helpers->createAndGetGuestSites($mm);
    } else {
      $sites = $mm_helpers->createAndGetSites($mm);
    }

    if ($mc_mm_data["site_id"]) {
      // If there's a site already chosen, we need to get and save it's script_src in WordPress
      $site = $mm_helpers->getSite($sites, $mc_mm_data["site_id"]);
      
      if ($site) {
        $mc_mm_data = array_merge(unserialize(get_option('mc_mm_data')), array("script_src" => $site->javascript_url));
        update_option("mc_mm_data", serialize($mc_mm_data));
      } else {
        // The chosen site does not exist in the mailmunch account any more, remove it locally
        $site_not_found = true;
        $mc_mm_data = unserialize(get_option('mc_mm_data'));
        unset($mc_mm_data["site_id"]);
        unset($mc_mm_data["script_src"]);
        update_option("mc_mm_data", serialize($mc_mm_data));
      }
    }

    if (!$mc_mm_data["site_id"]) {
      // If there's NO chosen site yet

      if (sizeof($sites) == 1 && ($sites[0]->name == get_bloginfo() || $sites[0]->name == "WordPress")) {
        // If this mailmunch account only has 1 site and its name matches this WordPress blogs

        $site = $sites[0];

        if ($site) {
          $mc_mm_data = array_merge(unserialize(get_option('mc_mm_data')), array("site_id" => $site->id, "script_src" => $site->javascript_url));
          update_option("mc_mm_data", serialize($mc_mm_data));
        }
      } else if (sizeof($sites) > 0) {
        // If this mailmunch account has one or more sites, let the user choose one
?>
  <div class="container">
    <div class="page-header">
      <h1>Choose Your Site</h1>
    </div>

    <p>Choose the site that you would like to link with your WordPress.</p>

    <form action="" method="POST">
      <div class="form-group">
        <input type="hidden" name="action" value="save_settings" />

        <select name="mc_mm_data[site_id]">
          <?php foreach ($sites as $site) { ?>
          <option value="<?php echo $site->id ?>"><?php echo $site->name ?></option>
          <?php } ?>
        </select>
      </div>

      <div class="form-group">
        <input type="submit" value="Save Settings" />
      </div>
    </form>
  </div>
<?php
        return;
      }
    }

    $request = $mm->getWidgetsHtml($mc_mm_data["site_id"]);
    $widgets = $request['body'];
    $widgets = str_replace("{{EMAIL}}", $mc_mm_email, $widgets);
    $widgets = str_replace("{{PASSWORD}}", $mc_mm_password, $widgets);
    echo $widgets;

    if ($mc_mm_guest_user) {
      $current_user = wp_get_current_user();
?>

<?php add_thickbox(); ?>

<a id="signup-box-btn" href="#TB_inline?width=450&height=450&inlineId=signup-signin-box" title="Create Account" class="thickbox" style="display: none;">Sign Up</a>

<div id="signup-signin-box" style="display:none;">
  <div id="sign-up-form" class="<?php if (!$_POST || ($_POST["action"] != "sign_in" && $_POST["action"] != "unlink_account")) { ?> active<?php } ?>">
    <div class="form-container">
      <p style="margin-bottom: 0px;">To activate your MailChimp forms, we will now create your account on MailMunch (<a onclick="showWhyAccount();" id="why-account-btn">Why?</a>).</p>

      <div id="why-account" class="alert alert-warning" style="display: none;">
        <h4>Why do I need a MailMunch account?</h4>

        <p>
          MailMunch is a not just a WordPress plugin but a standalone service. An account is required to identify your WordPress and serve your MailChimp forms.
        </p>
      </div>

      <?php if ($user_exists) { ?>
      <div id="invalid-alert" class="alert alert-danger" role="alert">Account with this email already exists. Please sign in using your password.</div>
      <?php } else if ($invalid_email) { ?>
      <div id="invalid-alert" class="alert alert-danger" role="alert">Invalid email. Please enter a valid email below.</div>
      <?php } ?>

      <form action="" method="POST">
        <input type="hidden" name="action" value="sign_up" />

        <div class="form-group">
          <label>Wordpress Name</label>
          <input type="text" placeholder="Site Name" name="site_name" value="<?php echo $mc_mm_data["site_name"] ?>" class="form-control">
        </div>

        <div class="form-group">
          <label>Wordpress URL</label>
          <input type="text" placeholder="Site URL" name="site_url" value="<?php echo $mc_mm_data["site_url"] ?>" class="form-control">
        </div>

        <div class="form-group">
          <label>Email Address</label>
          <input type="email" placeholder="Email Address" name="email" value="<?php echo $current_user->user_email ?>" class="form-control">
        </div>

        <div class="form-group">
          <input type="submit" value="Sign Up &raquo;" class="btn btn-success btn-lg" />
        </div>
      </form>
    </div>

    <p>Already have an account? <a id="show-sign-in" onclick="showSignInForm();">Sign In</a></p>
  </div>

  <div id="sign-in-form" class="<?php if ($_POST && ($_POST["action"] == "sign_in" || $_POST["action"] == "unlink_account")) { ?> active<?php } ?>">
    <p>Sign in using your email and password below.</p>

    <?php if ($_POST && $_POST["action"] == "sign_in") { ?>
    <div id="invalid-alert" class="alert alert-danger" role="alert">Invalid Email or Password. Please try again.</div>
    <?php } ?>

    <div class="form-container">
      <form action="" method="POST">
        <input type="hidden" name="action" value="sign_in" />

        <div class="form-group">
          <label>Email Address</label>
          <input type="email" placeholder="Email Address" name="email" class="form-control" value="<?php echo $_POST["email"] ?>" />
        </div>
        <div class="form-group">
          <label>Password</label>
          <input type="password" placeholder="Password" name="password" class="form-control" />
        </div>

        <div class="form-group">
          <input type="submit" value="Sign In &raquo;" class="btn btn-success btn-lg" />
        </div>
      </form>
    </div>

    <p>Forgot your password? <a href="http://<?php echo MAILCHIMP_MAILMUNCH_URL; ?>/users/password/new" target="_blank">Click here</a> to retrieve it.</p>
    <p>Don't have an account? <a id="show-sign-up" onclick="showSignUpForm();">Sign Up</a></p>
  </div>
</div>

<?php
      if ($_POST) { 
?>
<script>
jQuery(window).load(function() {
  showSignupBox();
});
</script>
<?php
      }
    }
  }
?>