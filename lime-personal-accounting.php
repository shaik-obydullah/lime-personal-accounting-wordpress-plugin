<?php
/**
 * Plugin Name: Lime Personal Accounting
 * Plugin URI: https://obydullah.com
 * Description: A personal accounting plugin with income, expense, wallet, and cashbook management.
 * Version: 1.0.0
 * Author: Obydullah
 * Author URI: https://obydullah.com
 * License: GPL v2 or later
 * Text Domain: lime-personal-accounting
 */

if (!defined('ABSPATH')) {
    exit;
}

define('LPA_VERSION', '1.0.0');
define('LPA_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('LPA_PLUGIN_URL', plugin_dir_url(__FILE__));
define('LPA_TABLE_PREFIX', 'lpa_');

require_once LPA_PLUGIN_DIR . 'includes/database.php';
require_once LPA_PLUGIN_DIR . 'includes/admin-menu.php';
require_once LPA_PLUGIN_DIR . 'includes/ajax-handlers.php';
require_once LPA_PLUGIN_DIR . 'includes/pages.php';

register_activation_hook(__FILE__, 'lpa_activate');
register_deactivation_hook(__FILE__, 'lpa_deactivate');

function lpa_activate() {
    lpa_create_tables();
}

function lpa_deactivate() {
    flush_rewrite_rules();
}

function lpa_init() {
    add_action('admin_enqueue_scripts', 'lpa_enqueue_assets');
    add_action('wp_ajax_lpa_action', 'lpa_ajax_router');
}
add_action('init', 'lpa_init');

function lpa_enqueue_assets($hook) {
    $screen = get_current_screen();
    if (!$screen || strpos($screen->id, 'lpa_') === false) {
        return;
    }
    wp_enqueue_style('lpa-style', LPA_PLUGIN_URL . 'assets/css/style.css', array(), LPA_VERSION);
    wp_enqueue_script('lpa-script', LPA_PLUGIN_URL . 'assets/js/script.js', array('jquery'), LPA_VERSION, true);
    wp_localize_script('lpa-script', 'lpaAjax', array(
        'ajaxurl' => admin_url('admin-ajax.php'),
        'nonce'   => wp_create_nonce('lpa_nonce'),
    ));
}
