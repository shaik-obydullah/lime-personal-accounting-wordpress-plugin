<?php
if (!defined('ABSPATH')) exit;

function opa_create_tables() {
    global $wpdb;
    $charset = $wpdb->get_charset_collate();

    $wallets = $wpdb->prefix . OPA_TABLE_PREFIX . 'wallets';
    $incomes = $wpdb->prefix . OPA_TABLE_PREFIX . 'incomes';
    $expenses = $wpdb->prefix . OPA_TABLE_PREFIX . 'expenses';
    $cashbook = $wpdb->prefix . OPA_TABLE_PREFIX . 'cashbook';
    $activities = $wpdb->prefix . OPA_TABLE_PREFIX . 'activities';
    $configurations = $wpdb->prefix . OPA_TABLE_PREFIX . 'configurations';

    $sql = "CREATE TABLE $wallets (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        name varchar(50) NOT NULL,
        category varchar(10) DEFAULT NULL,
        created_at datetime DEFAULT NULL,
        created_by bigint(20) DEFAULT NULL,
        updated_at datetime DEFAULT NULL,
        updated_by bigint(20) DEFAULT NULL,
        deleted_at datetime DEFAULT NULL,
        PRIMARY KEY (id)
    ) $charset;

    CREATE TABLE $incomes (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        fk_wallet_id bigint(20) DEFAULT NULL,
        description text NOT NULL,
        amount decimal(10,2) NOT NULL,
        currency varchar(30) DEFAULT NULL,
        created_at datetime DEFAULT NULL,
        created_by bigint(20) DEFAULT NULL,
        updated_at datetime DEFAULT NULL,
        updated_by bigint(20) DEFAULT NULL,
        deleted_at datetime DEFAULT NULL,
        PRIMARY KEY (id),
        KEY idx_incomes_wallet (fk_wallet_id),
        KEY idx_incomes_created_at (created_at),
        KEY idx_incomes_deleted_at (deleted_at)
    ) $charset;

    CREATE TABLE $expenses (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        fk_wallet_id bigint(20) DEFAULT NULL,
        description text NOT NULL,
        amount decimal(10,2) NOT NULL,
        currency varchar(30) DEFAULT NULL,
        created_at datetime DEFAULT NULL,
        created_by bigint(20) DEFAULT NULL,
        updated_at datetime DEFAULT NULL,
        updated_by bigint(20) DEFAULT NULL,
        deleted_at datetime DEFAULT NULL,
        PRIMARY KEY (id),
        KEY idx_expenses_wallet (fk_wallet_id),
        KEY idx_expenses_created_at (created_at),
        KEY idx_expenses_deleted_at (deleted_at)
    ) $charset;

    CREATE TABLE $cashbook (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        in_amount decimal(10,2) DEFAULT NULL,
        out_amount decimal(10,2) DEFAULT NULL,
        fk_reference_id bigint(20) NOT NULL,
        reference_type varchar(20) DEFAULT NULL,
        description text DEFAULT NULL,
        created_at datetime DEFAULT NULL,
        created_by bigint(20) DEFAULT NULL,
        updated_at datetime DEFAULT NULL,
        updated_by bigint(20) DEFAULT NULL,
        deleted_at datetime DEFAULT NULL,
        PRIMARY KEY (id),
        KEY idx_cashbook_reference (fk_reference_id),
        KEY idx_cashbook_deleted_at (deleted_at)
    ) $charset;

    CREATE TABLE $activities (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        fk_admin_id bigint(20) DEFAULT NULL,
        type varchar(10) NOT NULL,
        name varchar(150) NOT NULL,
        ip_address varchar(45) DEFAULT NULL,
        visitor_country varchar(50) DEFAULT NULL,
        visitor_state varchar(100) DEFAULT NULL,
        visitor_city varchar(100) DEFAULT NULL,
        visitor_address varchar(150) DEFAULT NULL,
        created_at datetime DEFAULT NULL,
        created_by bigint(20) DEFAULT NULL,
        deleted_at datetime DEFAULT NULL,
        PRIMARY KEY (id),
        KEY idx_activities_deleted_at (deleted_at)
    ) $charset;

    CREATE TABLE $configurations (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        name varchar(40) NOT NULL,
        setting text NOT NULL,
        created_at datetime DEFAULT NULL,
        created_by bigint(20) DEFAULT NULL,
        updated_at datetime DEFAULT NULL,
        updated_by bigint(20) DEFAULT NULL,
        deleted_at datetime DEFAULT NULL,
        PRIMARY KEY (id),
        UNIQUE KEY unique_config_name (name)
    ) $charset;";

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta($sql);

    update_option('opa_db_version', OPA_VERSION);
}

function opa_db() {
    global $wpdb;
    return $wpdb;
}

function opa_table($name) {
    global $wpdb;
    return $wpdb->prefix . 'opa_' . $name;
}

function opa_now() {
    return current_time('mysql');
}

function opa_user_id() {
    return get_current_user_id();
}

function opa_cache_group() {
    return 'opa_cache_' . opa_user_id();
}

function opa_flush_cache() {
    $group = opa_cache_group();
    wp_cache_delete('settings', $group);
    wp_cache_delete('summary', $group);
}
