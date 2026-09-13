<?php
if (!defined('ABSPATH')) exit;

add_action('admin_menu', 'lpa_register_menus');

function lpa_register_menus() {
    add_menu_page(
        'Lime Personal Accounting',
        'Accounting',
        'manage_options',
        'lpa_dashboard',
        'lpa_page_dashboard',
        'dashicons-money-alt',
        30
    );

    add_submenu_page('lpa_dashboard', 'Dashboard', 'Dashboard', 'manage_options', 'lpa_dashboard', 'lpa_page_dashboard');
    add_submenu_page('lpa_dashboard', 'Wallets', 'Wallets', 'manage_options', 'lpa_wallets', 'lpa_page_wallets');
    add_submenu_page('lpa_dashboard', 'Incomes', 'Incomes', 'manage_options', 'lpa_incomes', 'lpa_page_incomes');
    add_submenu_page('lpa_dashboard', 'Expenses', 'Expenses', 'manage_options', 'lpa_expenses', 'lpa_page_expenses');
    add_submenu_page('lpa_dashboard', 'Cashbook', 'Cashbook', 'manage_options', 'lpa_cashbook', 'lpa_page_cashbook');
    add_submenu_page('lpa_dashboard', 'Activities', 'Activities', 'manage_options', 'lpa_activities', 'lpa_page_activities');
    add_submenu_page('lpa_dashboard', 'Settings', 'Settings', 'manage_options', 'lpa_settings', 'lpa_page_settings');
}
