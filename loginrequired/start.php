<?php
/**
 * Log In Required plugin.
 *
 * @package LogInRequired
 * @license http://www.opensource.org/licenses/gpl-license.php
 * @author Khaled Afiouni
 * @copyright skinju.com 2010-2013
 * @link http://skinju.com/
 *
 * Upgraded to Elgg 1.9 by iionly in 2013
 */

elgg_register_event_handler('init','system','loginrequired_init');

function loginrequired_init() {
  // No need to do all the checking below if the user is already logged in... performance is key :)
  if (elgg_is_logged_in()) {
    return;
  }

  elgg_extend_view('css/elgg', 'loginrequired/css');

  elgg_unextend_view('page/elements/header', 'search/header');

  if (elgg_is_active_plugin('aalborg_theme')) {
    elgg_register_plugin_hook_handler('view', 'page/elements/navbar', 'suppress_aalborg_theme_navbar');
  }

  elgg_register_page_handler('','loginrequired_index');

  elgg_register_plugin_hook_handler('login_required','login_required', 'login_required_default_allowed_list');

  // Get the current page URL without any ? & parameters... this is required for the registration page to work properly
  $current_url = current_page_url();
  $parameters_start = strrpos($current_url, '?');
  if ($parameters_start)
    $current_url = substr($current_url, 0, $parameters_start);

  // Always allow index page
  if ($current_url == elgg_get_site_url()) {
    return;
  }

  $allow = array();
  // Allow should have pages
  $allow[] = '_graphics';
  $allow[] = 'action/login';
  $allow[] = 'register';
  $allow[] = 'action/register';
  $allow[] = 'forgotpassword';
  $allow[] = 'resetpassword';
  $allow[] = 'action/user/requestnewpassword';
  $allow[] = 'action/user/passwordreset';
  $allow[] = 'action/security/refreshtoken';
  $allow[] = 'ajax/view/js/languages';
  $allow[] = 'upgrade\.php';
  $allow[] = 'xml-rpc\.php';
  $allow[] = 'mt/mt-xmlrpc\.cgi';
  $allow[] = 'css/.*';
  $allow[] = 'js/.*';
  $allow[] = 'cache/.*';

  // Allow other plugin developers to edit the array values
  $add_allow = elgg_trigger_plugin_hook('login_required','login_required');

  // If more URL's are added... merge both with original list
  if (is_array($add_allow))
    $allow = array_merge($allow, $add_allow);

  // Any public_pages defined via Elgg's walled garden plugin hook?
  $plugins = elgg_trigger_plugin_hook('public_pages', 'walled_garden', NULL, array());

  // If more URL's are added... merge both with original list
  if (is_array($plugins))
    $allow = array_merge($allow, $plugins);

  // Check if current page is in allowed list... otherwise redirect to login
  foreach ($allow as $public) {
    $pattern = "`^".elgg_get_site_url().$public."/*$`i";
    if (preg_match($pattern, $current_url)) {
      return;
    }
  }

  gatekeeper();

}

function suppress_aalborg_theme_navbar($hook, $type, $return_value, $params) {

    return '';
}

// Add more allowed URL's...
function login_required_default_allowed_list($hook, $type, $returnvalue, $params) {
  global $CONFIG;

  // If externalpages plugin is active allow access to its pages
  $add = array();
  $add[] = 'terms/';
  $add[] = 'terms';
  $add[] = 'privacy/';
  $add[] = 'privacy';
  $add[] = 'about/';
  $add[] = 'about';

  return $add;
}

function loginrequired_index() {

        if (!include_once(dirname(__FILE__) . "/index.php")) {
                return false;
        }

        // return true to signify that we have handled the front page
        return true;
}
