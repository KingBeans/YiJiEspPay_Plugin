<?php
if (!isset($_SESSION['cqnew_payment_method_messages'])) {
  	zen_redirect(zen_href_link('index', '', 'SSL'));
  // die('error1!');
}
require(DIR_WS_MODULES . zen_get_module_directory('require_languages.php'));
// $breadcrumb->add(NAVBAR_TITLE);