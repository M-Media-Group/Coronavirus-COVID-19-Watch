<?php
// SO SAD
// BUT IT MUST BE DONE
// GOODBYE, FRIEND

// if uninstall.php is not called by WordPress, die
if (!defined('WP_UNINSTALL_PLUGIN')) {
    die;
}

delete_option('covid_setting_country');
delete_option('covid_setting_attribute');
delete_option('covid_setting_toolbar');
