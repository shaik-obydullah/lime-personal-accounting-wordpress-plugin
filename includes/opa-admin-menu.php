<?php
if (!defined('ABSPATH')) exit;

add_action('admin_menu', 'opa_register_menus');

function opa_register_menus() {
    add_menu_page(
        __('Obydullah Personal Accounting', 'obydullah-personal-accounting'),
        __('Accounting', 'obydullah-personal-accounting'),
        'manage_options',
        'opa_dashboard',
        'opa_page_dashboard',
        'dashicons-money-alt',
        30
    );

    add_submenu_page('opa_dashboard', __('Dashboard', 'obydullah-personal-accounting'), __('Dashboard', 'obydullah-personal-accounting'), 'manage_options', 'opa_dashboard', 'opa_page_dashboard');
    add_submenu_page('opa_dashboard', __('Wallets', 'obydullah-personal-accounting'), __('Wallets', 'obydullah-personal-accounting'), 'manage_options', 'opa_wallets', 'opa_page_wallets');
    add_submenu_page('opa_dashboard', __('Incomes', 'obydullah-personal-accounting'), __('Incomes', 'obydullah-personal-accounting'), 'manage_options', 'opa_incomes', 'opa_page_incomes');
    add_submenu_page('opa_dashboard', __('Expenses', 'obydullah-personal-accounting'), __('Expenses', 'obydullah-personal-accounting'), 'manage_options', 'opa_expenses', 'opa_page_expenses');
    add_submenu_page('opa_dashboard', __('Cashbook', 'obydullah-personal-accounting'), __('Cashbook', 'obydullah-personal-accounting'), 'manage_options', 'opa_cashbook', 'opa_page_cashbook');
    add_submenu_page('opa_dashboard', __('Activities', 'obydullah-personal-accounting'), __('Activities', 'obydullah-personal-accounting'), 'manage_options', 'opa_activities', 'opa_page_activities');
    add_submenu_page('opa_dashboard', __('Settings', 'obydullah-personal-accounting'), __('Settings', 'obydullah-personal-accounting'), 'manage_options', 'opa_settings', 'opa_page_settings');
}
