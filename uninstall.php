<?php
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

global $wpdb;
$opaac_tables = array('wallets', 'incomes', 'expenses', 'cashbook', 'activities', 'configurations');
foreach ($opaac_tables as $opaac_table) {
    $wpdb->query('DROP TABLE IF EXISTS `' . $wpdb->prefix . 'opaac_' . $opaac_table . '`'); // phpcs:ignore WordPress.DB
}
delete_option('opaac_db_version');