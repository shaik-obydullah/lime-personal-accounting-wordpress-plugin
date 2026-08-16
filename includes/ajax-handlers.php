<?php
if (!defined('ABSPATH')) exit;

function lpa_ajax_router() {
    check_ajax_referer('lpa_nonce', 'nonce');

    if (!current_user_can('manage_options')) {
        wp_send_json_error(array('message' => 'Unauthorized'));
    }

    $action = isset($_POST['lpa_action']) ? sanitize_text_field($_POST['lpa_action']) : '';
    $data = isset($_POST['data']) ? $_POST['data'] : array();

    switch ($action) {
        case 'get_wallets':
            lpa_get_wallets();
            break;
        case 'save_wallet':
            lpa_save_wallet($data);
            break;
        case 'delete_wallet':
            lpa_delete_wallet($data);
            break;
        case 'get_incomes':
            lpa_get_incomes();
            break;
        case 'save_income':
            lpa_save_income($data);
            break;
        case 'delete_income':
            lpa_delete_income($data);
            break;
        case 'get_expenses':
            lpa_get_expenses();
            break;
        case 'save_expense':
            lpa_save_expense($data);
            break;
        case 'delete_expense':
            lpa_delete_expense($data);
            break;
        case 'get_cashbook':
            lpa_get_cashbook();
            break;
        case 'get_activities':
            lpa_get_activities();
            break;
        case 'get_settings':
            lpa_get_settings();
            break;
        default:
            wp_send_json_error(array('message' => 'Unknown action'));
    }
}

function lpa_get_wallets() {
    $db = lpa_db();
    $table = lpa_table('wallets');
    $results = $db->get_results("SELECT * FROM $table WHERE deleted_at IS NULL ORDER BY id DESC", ARRAY_A);
    wp_send_json_success($results);
}

function lpa_save_wallet($data) {
    $db = lpa_db();
    $table = lpa_table('wallets');
    $id = isset($data['id']) ? absint($data['id']) : 0;
    $name = sanitize_text_field($data['name']);
    $category = sanitize_text_field($data['category']);
    $now = lpa_now();
    $user = lpa_user_id();

    if ($id > 0) {
        $db->update($table, array(
            'name' => $name,
            'category' => $category,
            'updated_at' => $now,
            'updated_by' => $user,
        ), array('id' => $id));
        wp_send_json_success(array('message' => 'Wallet updated'));
    } else {
        $db->insert($table, array(
            'name' => $name,
            'category' => $category,
            'created_at' => $now,
            'created_by' => $user,
        ));
        wp_send_json_success(array('message' => 'Wallet created', 'id' => $db->insert_id));
    }
}

function lpa_delete_wallet($data) {
    $db = lpa_db();
    $table = lpa_table('wallets');
    $id = absint($data['id']);
    $db->update($table, array('deleted_at' => lpa_now()), array('id' => $id));
    wp_send_json_success(array('message' => 'Wallet deleted'));
}

function lpa_get_incomes() {
    $db = lpa_db();
    $table = lpa_table('incomes');
    $wallet_table = lpa_table('wallets');
    $results = $db->get_results("
        SELECT i.*, w.name AS wallet_name 
        FROM $table i 
        LEFT JOIN $wallet_table w ON i.fk_wallet_id = w.id 
        WHERE i.deleted_at IS NULL 
        ORDER BY i.id DESC
    ", ARRAY_A);
    wp_send_json_success($results);
}

function lpa_save_income($data) {
    $db = lpa_db();
    $table = lpa_table('incomes');
    $cashbook_table = lpa_table('cashbook');
    $id = isset($data['id']) ? absint($data['id']) : 0;
    $wallet_id = absint($data['wallet_id']);
    $amount = floatval($data['amount']);
    $description = sanitize_textarea_field($data['description']);
    $currency = sanitize_text_field($data['currency']);
    $now = lpa_now();
    $user = lpa_user_id();

    if ($id > 0) {
        $db->update($table, array(
            'fk_wallet_id' => $wallet_id,
            'amount' => $amount,
            'description' => $description,
            'currency' => $currency,
            'updated_at' => $now,
            'updated_by' => $user,
        ), array('id' => $id));
        wp_send_json_success(array('message' => 'Income updated'));
    } else {
        $db->insert($table, array(
            'fk_wallet_id' => $wallet_id,
            'amount' => $amount,
            'description' => $description,
            'currency' => $currency,
            'created_at' => $now,
            'created_by' => $user,
        ));
        $income_id = $db->insert_id;

        $db->insert($cashbook_table, array(
            'in_amount' => $amount,
            'fk_reference_id' => $income_id,
            'reference_type' => 'income',
            'created_at' => $now,
            'created_by' => $user,
        ));

        lpa_log_activity('success', 'Income Added: ' . $amount);

        wp_send_json_success(array('message' => 'Income created', 'id' => $income_id));
    }
}

function lpa_delete_income($data) {
    $db = lpa_db();
    $table = lpa_table('incomes');
    $cashbook_table = lpa_table('cashbook');
    $id = absint($data['id']);
    $now = lpa_now();

    $db->update($table, array('deleted_at' => $now), array('id' => $id));
    $db->update($cashbook_table, array('deleted_at' => $now), array('fk_reference_id' => $id, 'reference_type' => 'income'));

    wp_send_json_success(array('message' => 'Income deleted'));
}

function lpa_get_expenses() {
    $db = lpa_db();
    $table = lpa_table('expenses');
    $wallet_table = lpa_table('wallets');
    $results = $db->get_results("
        SELECT e.*, w.name AS wallet_name 
        FROM $table e 
        LEFT JOIN $wallet_table w ON e.fk_wallet_id = w.id 
        WHERE e.deleted_at IS NULL 
        ORDER BY e.id DESC
    ", ARRAY_A);
    wp_send_json_success($results);
}

function lpa_save_expense($data) {
    $db = lpa_db();
    $table = lpa_table('expenses');
    $cashbook_table = lpa_table('cashbook');
    $id = isset($data['id']) ? absint($data['id']) : 0;
    $wallet_id = absint($data['wallet_id']);
    $amount = floatval($data['amount']);
    $description = sanitize_textarea_field($data['description']);
    $currency = sanitize_text_field($data['currency']);
    $now = lpa_now();
    $user = lpa_user_id();

    if ($id > 0) {
        $db->update($table, array(
            'fk_wallet_id' => $wallet_id,
            'amount' => $amount,
            'description' => $description,
            'currency' => $currency,
            'updated_at' => $now,
            'updated_by' => $user,
        ), array('id' => $id));
        wp_send_json_success(array('message' => 'Expense updated'));
    } else {
        $db->insert($table, array(
            'fk_wallet_id' => $wallet_id,
            'amount' => $amount,
            'description' => $description,
            'currency' => $currency,
            'created_at' => $now,
            'created_by' => $user,
        ));
        $expense_id = $db->insert_id;

        $db->insert($cashbook_table, array(
            'out_amount' => $amount,
            'fk_reference_id' => $expense_id,
            'reference_type' => 'expense',
            'created_at' => $now,
            'created_by' => $user,
        ));

        lpa_log_activity('warning', 'Expense Added: ' . $amount);

        wp_send_json_success(array('message' => 'Expense created', 'id' => $expense_id));
    }
}

function lpa_delete_expense($data) {
    $db = lpa_db();
    $table = lpa_table('expenses');
    $cashbook_table = lpa_table('cashbook');
    $id = absint($data['id']);
    $now = lpa_now();

    $db->update($table, array('deleted_at' => $now), array('id' => $id));
    $db->update($cashbook_table, array('deleted_at' => $now), array('fk_reference_id' => $id, 'reference_type' => 'expense'));

    wp_send_json_success(array('message' => 'Expense deleted'));
}

function lpa_get_cashbook() {
    $db = lpa_db();
    $table = lpa_table('cashbook');
    $results = $db->get_results("SELECT * FROM $table WHERE deleted_at IS NULL ORDER BY id DESC", ARRAY_A);
    wp_send_json_success($results);
}

function lpa_get_activities() {
    $db = lpa_db();
    $table = lpa_table('activities');
    $results = $db->get_results("SELECT * FROM $table WHERE deleted_at IS NULL ORDER BY id DESC LIMIT 50", ARRAY_A);
    wp_send_json_success($results);
}

function lpa_get_settings() {
    $db = lpa_db();
    $table = lpa_table('configurations');
    $results = $db->get_results("SELECT * FROM $table WHERE deleted_at IS NULL ORDER BY id ASC", ARRAY_A);
    wp_send_json_success($results);
}

function lpa_log_activity($type, $name) {
    $db = lpa_db();
    $table = lpa_table('activities');
    $db->insert($table, array(
        'fk_admin_id' => lpa_user_id(),
        'type' => $type,
        'name' => $name,
        'ip_address' => $_SERVER['REMOTE_ADDR'] ?? '',
        'created_at' => lpa_now(),
        'created_by' => lpa_user_id(),
    ));
}
