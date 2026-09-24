<?php
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

global $wpdb;
$tables = array('wallets', 'incomes', 'expenses', 'cashbook', 'activities', 'configurations');
foreach ($tables as $table) {
    $wpdb->query('DROP TABLE IF EXISTS `' . $wpdb->prefix . 'opaac_' . $table . '`'); // phpcs:ignore WordPress.DB
}
delete_option('opaac_db_version');