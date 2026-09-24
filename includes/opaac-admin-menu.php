<?php
if (!defined('ABSPATH')) exit;

add_action('admin_menu', 'opaac_register_menus');

function opaac_register_menus() {
    add_menu_page(
        __('Obydullah Personal Accounting', 'obydullah-personal-accounting'),
        __('Accounting', 'obydullah-personal-accounting'),
        'manage_options',
        'opaac_dashboard',
        'opaac_page_dashboard',
        'dashicons-money-alt',
        100
    );

    add_submenu_page('opaac_dashboard', __('Dashboard', 'obydullah-personal-accounting'), __('Dashboard', 'obydullah-personal-accounting'), 'manage_options', 'opaac_dashboard', 'opaac_page_dashboard');
    add_submenu_page('opaac_dashboard', __('Wallets', 'obydullah-personal-accounting'), __('Wallets', 'obydullah-personal-accounting'), 'manage_options', 'opaac_wallets', 'opaac_page_wallets');
    add_submenu_page('opaac_dashboard', __('Incomes', 'obydullah-personal-accounting'), __('Incomes', 'obydullah-personal-accounting'), 'manage_options', 'opaac_incomes', 'opaac_page_incomes');
    add_submenu_page('opaac_dashboard', __('Expenses', 'obydullah-personal-accounting'), __('Expenses', 'obydullah-personal-accounting'), 'manage_options', 'opaac_expenses', 'opaac_page_expenses');
    add_submenu_page('opaac_dashboard', __('Cashbook', 'obydullah-personal-accounting'), __('Cashbook', 'obydullah-personal-accounting'), 'manage_options', 'opaac_cashbook', 'opaac_page_cashbook');
    add_submenu_page('opaac_dashboard', __('Activities', 'obydullah-personal-accounting'), __('Activities', 'obydullah-personal-accounting'), 'manage_options', 'opaac_activities', 'opaac_page_activities');
    add_submenu_page('opaac_dashboard', __('Settings', 'obydullah-personal-accounting'), __('Settings', 'obydullah-personal-accounting'), 'manage_options', 'opaac_settings', 'opaac_page_settings');
}