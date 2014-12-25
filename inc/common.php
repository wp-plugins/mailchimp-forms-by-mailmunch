<?php
class MailchimpMailmunchHelpers {
  function __construct() {
  }

  function getEmailPassword() {
    $mc_mm_email = get_option("mc_mm_user_email");
    $mc_mm_password = get_option("mc_mm_user_password");

    if (empty($mc_mm_email)) {
      $current_user = wp_get_current_user();
      update_option("mc_mm_user_email", $current_user->user_email);
    }

    if (empty($mc_mm_password)) {
      update_option("mc_mm_user_password", base64_encode(uniqid()));
    }

    $mc_mm_email = get_option("mc_mm_user_email");
    $mc_mm_password = get_option("mc_mm_user_password");

    return array('email' => $mc_mm_email, 'password' => $mc_mm_password);
  }

  function getSite($sites, $site_id) {
    foreach ($sites as $s) {
      if ($s->id == intval($site_id)) {
        $site = $s;
        break;
      }
    }

    return (isset($site) ? $site : false);
  }

  function createAndGetSites($mm) {
    $site_url = home_url();
    $site_name = get_bloginfo();

    if (!$mm->hasSite()) {
      $mm->createSite($site_name, $site_url);
    }
    $request = $mm->sites();
    if ($request['response']['code'] == 200){
      $sites = $request['body'];

      return json_decode($sites);
    }
    else {
      return array();
    }
  }

  function createAndGetGuestSites($mm) {
    // This is for GUEST users. Do NOT collect any user data.
    $site_url = "";
    $site_name = "WordPress";

    if (!$mm->hasSite()) {
      $mm->createSite($site_name, $site_url);
    }
    $request = $mm->sites();
    if ($request['response']['code'] == 200){
      $sites = $request['body'];

      return json_decode($sites);
    }
    else {
      return array();
    }
  }
}
?>
