<?php
/**
 * Plugin Name: Obydullah Personal Accounting
 * Plugin URI: https://obydullah.com/project/lime-personal-accounting-wordpress-plugin
 * Description: Track personal income, expenses, wallets, and automatic cashbook ledgers - all inside your WordPress admin.
 * Version: 1.0.0
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

define('OPAAC_VERSION', '1.0.0');
define('OPAAC_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('OPAAC_PLUGIN_URL', plugin_dir_url(__FILE__));
define('OPAAC_TABLE_PREFIX', 'opaac_');

require_once OPAAC_PLUGIN_DIR . 'includes/opaac-database.php';
require_once OPAAC_PLUGIN_DIR . 'includes/opaac-admin-menu.php';
require_once OPAAC_PLUGIN_DIR . 'includes/opaac-ajax-handlers.php';
require_once OPAAC_PLUGIN_DIR . 'includes/opaac-pages.php';

register_activation_hook(__FILE__, 'opaac_activate');
register_deactivation_hook(__FILE__, 'opaac_deactivate');

function opaac_activate() {
    opaac_create_tables();
}

function opaac_deactivate() {
    flush_rewrite_rules();
}

function opaac_init() {
    add_action('admin_enqueue_scripts', 'opaac_enqueue_assets');
    add_action('wp_ajax_opaac_action', 'opaac_ajax_router');
    add_action('admin_init', 'opaac_maybe_create_tables');
}
add_action('init', 'opaac_init');

function opaac_maybe_create_tables() {
    if (get_option('opaac_db_version') !== OPAAC_VERSION) {
        opaac_create_tables();
    }
}

function opaac_enqueue_assets($hook) {
    $screen = get_current_screen();
    if (!$screen || strpos($screen->id, 'opaac_') === false) {
        return;
    }
    wp_enqueue_style('opaac-style', OPAAC_PLUGIN_URL . 'assets/css/opaac-style.css', array(), filemtime(OPAAC_PLUGIN_DIR . 'assets/css/opaac-style.css'));
    wp_enqueue_script('opaac-script', OPAAC_PLUGIN_URL . 'assets/js/opaac-script.js', array('jquery'), filemtime(OPAAC_PLUGIN_DIR . 'assets/js/opaac-script.js'), true);
    wp_localize_script('opaac-script', 'opaacAjax', array(
        'ajaxurl' => admin_url('admin-ajax.php'),
        'nonce'   => wp_create_nonce('opaac_nonce'),
    ));
}