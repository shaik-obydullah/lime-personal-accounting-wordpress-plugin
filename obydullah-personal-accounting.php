<?php
/**
 * Plugin Name: Obydullah Personal Accounting
 * Plugin URI: https://obydullah.com/project/lime-personal-accounting-wordpress-plugin
 * Description: Track personal income, expenses, wallets, and automatic cashbook ledgers - all inside your WordPress admin.
 * Author: Shaik Obydullah
 * Author URI: https://obydullah.com
 * Text Domain: obydullah-personal-accounting
 * Domain Path: /languages
 * Requires at least: 6.0
 * Requires PHP: 8.0
 * License: GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

if (!defined('ABSPATH')) {
    exit;
}

define('OPA_VERSION', '1.0.0');
define('OPA_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('OPA_PLUGIN_URL', plugin_dir_url(__FILE__));
define('OPA_TABLE_PREFIX', 'opa_');

require_once OPA_PLUGIN_DIR . 'includes/opa-database.php';
require_once OPA_PLUGIN_DIR . 'includes/opa-admin-menu.php';
require_once OPA_PLUGIN_DIR . 'includes/opa-ajax-handlers.php';
require_once OPA_PLUGIN_DIR . 'includes/opa-pages.php';

register_activation_hook(__FILE__, 'opa_activate');
register_deactivation_hook(__FILE__, 'opa_deactivate');

function opa_activate() {
    opa_create_tables();
}

function opa_deactivate() {
    flush_rewrite_rules();
}

function opa_init() {
    add_action('admin_enqueue_scripts', 'opa_enqueue_assets');
    add_action('wp_ajax_opa_action', 'opa_ajax_router');
    add_action('admin_init', 'opa_maybe_create_tables');
}
add_action('init', 'opa_init');

function opa_maybe_create_tables() {
    if (get_option('opa_db_version') !== OPA_VERSION) {
        opa_create_tables();
    }
}

function opa_enqueue_assets($hook) {
    $screen = get_current_screen();
    if (!$screen || strpos($screen->id, 'opa_') === false) {
        return;
    }
    wp_enqueue_style('opa-style', OPA_PLUGIN_URL . 'assets/css/opa-style.css', array(), filemtime(OPA_PLUGIN_DIR . 'assets/css/opa-style.css'));
    wp_enqueue_script('opa-script', OPA_PLUGIN_URL . 'assets/js/opa-script.js', array('jquery'), filemtime(OPA_PLUGIN_DIR . 'assets/js/opa-script.js'), true);
    wp_localize_script('opa-script', 'opaAjax', array(
        'ajaxurl' => admin_url('admin-ajax.php'),
        'nonce'   => wp_create_nonce('opa_nonce'),
    ));
}