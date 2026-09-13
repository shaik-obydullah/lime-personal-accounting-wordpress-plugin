<?php
if (!defined('ABSPATH')) exit;

function opa_ajax_router() {
    check_ajax_referer('opa_nonce', 'nonce');

    if (!current_user_can('manage_options')) {
        wp_send_json_error(array('message' => __('Unauthorized', 'obydullah-personal-accounting')));
    }

    $action = isset($_POST['opa_action']) ? sanitize_text_field(wp_unslash($_POST['opa_action'])) : (isset($_GET['opa_action']) ? sanitize_text_field(wp_unslash($_GET['opa_action'])) : '');
    $data = isset($_POST['data']) ? wp_unslash($_POST['data']) : array(); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- each field is sanitized by the handler.

    switch ($action) {
        case 'get_wallets':
            opa_get_wallets();
            break;
        case 'save_wallet':
            opa_save_wallet($data);
            break;
        case 'delete_wallet':
            opa_delete_wallet($data);
            break;
        case 'get_incomes':
            opa_get_incomes();
            break;
        case 'save_income':
            opa_save_income($data);
            break;
        case 'delete_income':
            opa_delete_income($data);
            break;
        case 'get_expenses':
            opa_get_expenses();
            break;
        case 'save_expense':
            opa_save_expense($data);
            break;
        case 'delete_expense':
            opa_delete_expense($data);
            break;
        case 'get_cashbook':
            opa_get_cashbook();
            break;
        case 'get_activities':
            opa_get_activities();
            break;
        case 'get_settings':
            opa_get_settings();
            break;
        case 'save_settings':
            opa_save_settings($data);
            break;
        case 'reset_data':
            opa_reset_data();
            break;
        case 'export_csv':
            opa_export_csv();
            break;
        default:
            wp_send_json_error(array('message' => __('Unknown action', 'obydullah-personal-accounting')));
    }
}

function opa_get_wallets() {
    $db = opa_db();
    $table = opa_table('wallets');
    $results = $db->get_results(
        $db->prepare(
            "SELECT * FROM {$table} WHERE 1 = %d AND deleted_at IS NULL ORDER BY id DESC",
            1
        ),
        ARRAY_A
    );
    wp_send_json_success($results);
}

function opa_save_wallet($data) {
    $db = opa_db();
    $table = opa_table('wallets');
    $id = isset($data['id']) ? absint($data['id']) : 0;
    $name = sanitize_text_field($data['name'] ?? '');
    $category = sanitize_text_field($data['category'] ?? '');
    $now = opa_now();
    $user = opa_user_id();

    if ($id > 0) {
        $db->update($table, array(
            'name' => $name,
            'category' => $category,
            'updated_at' => $now,
            'updated_by' => $user,
        ), array('id' => $id), array('%s', '%s', '%s', '%d'), array('%d'));
        opa_flush_cache();
        wp_send_json_success(array('message' => __('Wallet updated', 'obydullah-personal-accounting')));
    } else {
        $db->insert($table, array(
            'name' => $name,
            'category' => $category,
            'created_at' => $now,
            'created_by' => $user,
        ), array('%s', '%s', '%s', '%d'));
        opa_flush_cache();
        wp_send_json_success(array('message' => __('Wallet created', 'obydullah-personal-accounting'), 'id' => $db->insert_id));
    }
}

function opa_delete_wallet($data) {
    $db = opa_db();
    $table = opa_table('wallets');
    $id = absint($data['id'] ?? 0);
    $db->update($table, array('deleted_at' => opa_now()), array('id' => $id), array('%s'), array('%d'));
    opa_flush_cache();
    wp_send_json_success(array('message' => __('Wallet deleted', 'obydullah-personal-accounting')));
}

function opa_get_incomes() {
    $db = opa_db();
    $table = opa_table('incomes');
    $wallet_table = opa_table('wallets');
    $results = $db->get_results(
        $db->prepare(
            "SELECT i.*, w.name AS wallet_name
            FROM {$table} i
            LEFT JOIN {$wallet_table} w ON i.fk_wallet_id = w.id
            WHERE 1 = %d AND i.deleted_at IS NULL
            ORDER BY i.id DESC",
            1
        ),
        ARRAY_A
    );
    wp_send_json_success($results);
}

function opa_save_income($data) {
    $db = opa_db();
    $table = opa_table('incomes');
    $cashbook_table = opa_table('cashbook');
    $id = isset($data['id']) ? absint($data['id']) : 0;
    $wallet_id = absint($data['wallet_id'] ?? 0);
    $amount = floatval($data['amount'] ?? 0);
    $description = sanitize_textarea_field($data['description'] ?? '');
    $currency = sanitize_text_field($data['currency'] ?? '');
    $now = opa_now();
    $user = opa_user_id();

    if ($id > 0) {
        $db->update($table, array(
            'fk_wallet_id' => $wallet_id,
            'amount' => $amount,
            'description' => $description,
            'currency' => $currency,
            'updated_at' => $now,
            'updated_by' => $user,
        ), array('id' => $id), array('%d', '%f', '%s', '%s', '%s', '%d'), array('%d'));
        opa_flush_cache();
        wp_send_json_success(array('message' => __('Income updated', 'obydullah-personal-accounting')));
    } else {
        $db->insert($table, array(
            'fk_wallet_id' => $wallet_id,
            'amount' => $amount,
            'description' => $description,
            'currency' => $currency,
            'created_at' => $now,
            'created_by' => $user,
        ), array('%d', '%f', '%s', '%s', '%s', '%d'));
        $income_id = $db->insert_id;
        opa_flush_cache();

        $db->insert($cashbook_table, array(
            'in_amount' => $amount,
            'fk_reference_id' => $income_id,
            'reference_type' => 'income',
            'created_at' => $now,
            'created_by' => $user,
        ), array('%f', '%d', '%s', '%s', '%d'));

        /* translators: %s: income amount. */
        opa_log_activity('success', sprintf(__('Income Added: %s', 'obydullah-personal-accounting'), $amount));

        wp_send_json_success(array('message' => __('Income created', 'obydullah-personal-accounting'), 'id' => $income_id));
    }
}

function opa_delete_income($data) {
    $db = opa_db();
    $table = opa_table('incomes');
    $cashbook_table = opa_table('cashbook');
    $id = absint($data['id'] ?? 0);
    $now = opa_now();

    $db->update($table, array('deleted_at' => $now), array('id' => $id), array('%s'), array('%d'));
    $db->update($cashbook_table, array('deleted_at' => $now), array('fk_reference_id' => $id, 'reference_type' => 'income'), array('%s'), array('%d', '%s'));
    opa_flush_cache();

    wp_send_json_success(array('message' => __('Income deleted', 'obydullah-personal-accounting')));
}

function opa_get_expenses() {
    $db = opa_db();
    $table = opa_table('expenses');
    $wallet_table = opa_table('wallets');
    $results = $db->get_results(
        $db->prepare(
            "SELECT e.*, w.name AS wallet_name
            FROM {$table} e
            LEFT JOIN {$wallet_table} w ON e.fk_wallet_id = w.id
            WHERE 1 = %d AND e.deleted_at IS NULL
            ORDER BY e.id DESC",
            1
        ),
        ARRAY_A
    );
    wp_send_json_success($results);
}

function opa_save_expense($data) {
    $db = opa_db();
    $table = opa_table('expenses');
    $cashbook_table = opa_table('cashbook');
    $id = isset($data['id']) ? absint($data['id']) : 0;
    $wallet_id = absint($data['wallet_id'] ?? 0);
    $amount = floatval($data['amount'] ?? 0);
    $description = sanitize_textarea_field($data['description'] ?? '');
    $currency = sanitize_text_field($data['currency'] ?? '');
    $now = opa_now();
    $user = opa_user_id();

    if ($id > 0) {
        $db->update($table, array(
            'fk_wallet_id' => $wallet_id,
            'amount' => $amount,
            'description' => $description,
            'currency' => $currency,
            'updated_at' => $now,
            'updated_by' => $user,
        ), array('id' => $id), array('%d', '%f', '%s', '%s', '%s', '%d'), array('%d'));
        opa_flush_cache();
        wp_send_json_success(array('message' => __('Expense updated', 'obydullah-personal-accounting')));
    } else {
        $db->insert($table, array(
            'fk_wallet_id' => $wallet_id,
            'amount' => $amount,
            'description' => $description,
            'currency' => $currency,
            'created_at' => $now,
            'created_by' => $user,
        ), array('%d', '%f', '%s', '%s', '%s', '%d'));
        $expense_id = $db->insert_id;
        opa_flush_cache();

        $db->insert($cashbook_table, array(
            'out_amount' => $amount,
            'fk_reference_id' => $expense_id,
            'reference_type' => 'expense',
            'created_at' => $now,
            'created_by' => $user,
        ), array('%f', '%d', '%s', '%s', '%d'));

        /* translators: %s: expense amount. */
        opa_log_activity('warning', sprintf(__('Expense Added: %s', 'obydullah-personal-accounting'), $amount));

        wp_send_json_success(array('message' => __('Expense created', 'obydullah-personal-accounting'), 'id' => $expense_id));
    }
}

function opa_delete_expense($data) {
    $db = opa_db();
    $table = opa_table('expenses');
    $cashbook_table = opa_table('cashbook');
    $id = absint($data['id'] ?? 0);
    $now = opa_now();

    $db->update($table, array('deleted_at' => $now), array('id' => $id), array('%s'), array('%d'));
    $db->update($cashbook_table, array('deleted_at' => $now), array('fk_reference_id' => $id, 'reference_type' => 'expense'), array('%s'), array('%d', '%s'));
    opa_flush_cache();

    wp_send_json_success(array('message' => __('Expense deleted', 'obydullah-personal-accounting')));
}

function opa_get_cashbook() {
    $db = opa_db();
    $table = opa_table('cashbook');
    $results = $db->get_results(
        $db->prepare(
            "SELECT * FROM {$table} WHERE 1 = %d AND deleted_at IS NULL ORDER BY id DESC",
            1
        ),
        ARRAY_A
    );
    wp_send_json_success($results);
}

function opa_get_activities() {
    $db = opa_db();
    $table = opa_table('activities');
    $results = $db->get_results(
        $db->prepare(
            "SELECT * FROM {$table} WHERE 1 = %d AND deleted_at IS NULL ORDER BY id DESC LIMIT 50",
            1
        ),
        ARRAY_A
    );
    wp_send_json_success($results);
}

function opa_get_settings() {
    $group = opa_cache_group();
    $results = wp_cache_get('settings', $group);
    if (false === $results) {
        $db = opa_db();
        $table = opa_table('configurations');
        $results = $db->get_results(
            $db->prepare(
                "SELECT * FROM {$table} WHERE 1 = %d AND deleted_at IS NULL ORDER BY id ASC",
                1
            ),
            ARRAY_A
        );
        wp_cache_set('settings', $results, $group, 300);
    }
    wp_send_json_success($results);
}

function opa_get_setting($name, $default = '') {
    $db = opa_db();
    $table = opa_table('configurations');
    $stored = $db->get_var(
        $db->prepare(
            "SELECT setting FROM {$table} WHERE name = %s AND deleted_at IS NULL LIMIT 1",
            $name
        )
    );
    if (null === $stored) {
        return $default;
    }
    $value = json_decode($stored, true);
    return (null === $value) ? $stored : $value;
}

function opa_set_setting($name, $value) {
    $db = opa_db();
    $table = opa_table('configurations');
    $name = sanitize_key($name);
    $setting = wp_json_encode($value);
    $now = opa_now();
    $user = opa_user_id();

    $id = (int) $db->get_var(
        $db->prepare(
            "SELECT id FROM {$table} WHERE name = %s AND deleted_at IS NULL LIMIT 1",
            $name
        )
    );

    if ($id > 0) {
        $db->update($table, array(
            'setting'   => $setting,
            'updated_at' => $now,
            'updated_by' => $user,
        ), array('id' => $id), array('%s', '%s', '%d'), array('%d'));
    } else {
        $db->insert($table, array(
            'name'       => $name,
            'setting'    => $setting,
            'created_at' => $now,
            'created_by' => $user,
        ), array('%s', '%s', '%s', '%d'));
    }

    wp_cache_delete('settings', opa_cache_group());
}

function opa_save_settings($data) {
    $currency = strtoupper(sanitize_text_field($data['default_currency'] ?? ''));
    if ('' === $currency) {
        $currency = 'USD';
    }
    $currency_symbol = sanitize_text_field($data['currency_symbol'] ?? '');
    $page_size = absint($data['page_size'] ?? 10);
    $page_size = max(5, min(100, $page_size));
    $default_wallet_id = absint($data['default_wallet_id'] ?? 0);

    opa_set_setting('default_currency', $currency);
    opa_set_setting('currency_symbol', $currency_symbol);
    opa_set_setting('page_size', $page_size);
    opa_set_setting('default_wallet_id', $default_wallet_id);

    $db = opa_db();
    if (!empty($db->last_error)) {
        wp_send_json_error(array('message' => $db->last_error));
    }

    $message = __('Settings saved', 'obydullah-personal-accounting');
    opa_log_activity('success', $message);
    wp_send_json_success(array('message' => $message));
}

function opa_reset_data() {
    $db = opa_db();
    $tables = array('wallets', 'incomes', 'expenses', 'cashbook', 'activities', 'configurations');
    foreach ($tables as $name) {
        $table = opa_table($name);
        $db->query(
            $db->prepare(
                "DELETE FROM `{$table}` WHERE 1 = %d",
                1
            )
        );
    }
    opa_flush_cache();

    /* translators: %s: current date. */
    $message = sprintf(__('All data wiped on %s', 'obydullah-personal-accounting'), opa_now());
    opa_log_activity('warning', $message);
    wp_send_json_success(array('message' => __('All data wiped', 'obydullah-personal-accounting')));
}

function opa_export_csv() {
    $db = opa_db();
    $wallet_table = opa_table('wallets');
    $incomes_table = opa_table('incomes');
    $expenses_table = opa_table('expenses');

    $incomes = $db->get_results(
        $db->prepare(
            "SELECT i.amount, i.currency, i.description, i.created_at AS entry_date, w.name AS wallet_name, 'income' AS type
            FROM {$incomes_table} i
            LEFT JOIN {$wallet_table} w ON i.fk_wallet_id = w.id
            WHERE 1 = %d AND i.deleted_at IS NULL
            ORDER BY i.id ASC",
            1
        ),
        ARRAY_A
    );
    $expenses = $db->get_results(
        $db->prepare(
            "SELECT e.amount, e.currency, e.description, e.created_at AS entry_date, w.name AS wallet_name, 'expense' AS type
            FROM {$expenses_table} e
            LEFT JOIN {$wallet_table} w ON e.fk_wallet_id = w.id
            WHERE 1 = %d AND e.deleted_at IS NULL
            ORDER BY e.id ASC",
            1
        ),
        ARRAY_A
    );
    $rows = array_merge($incomes, $expenses);

    header('Content-Type: text/csv; charset=UTF-8');
    /* translators: %s: current date. */
    header('Content-Disposition: attachment; filename="opa-accounting-export-' . wp_date('Y-m-d') . '.csv"');

    $out = fopen('php://output', 'w');
    fputcsv($out, array(__('Type', 'obydullah-personal-accounting'), __('Wallet', 'obydullah-personal-accounting'), __('Amount', 'obydullah-personal-accounting'), __('Currency', 'obydullah-personal-accounting'), __('Description', 'obydullah-personal-accounting'), __('Date', 'obydullah-personal-accounting')));
    foreach ($rows as $row) {
        fputcsv($out, array(
            $row['type'],
            $row['wallet_name'],
            $row['amount'],
            $row['currency'],
            $row['description'],
            $row['entry_date'],
        ));
    }
    wp_die();
}

function opa_log_activity($type, $name) {
    $db = opa_db();
    $table = opa_table('activities');
    $ip_address = filter_var(wp_unslash($_SERVER['REMOTE_ADDR'] ?? ''), FILTER_VALIDATE_IP);
    $db->insert($table, array(
        'fk_admin_id' => opa_user_id(),
        'type' => sanitize_text_field($type),
        'name' => sanitize_text_field($name),
        'ip_address' => $ip_address ? $ip_address : '',
        'created_at' => opa_now(),
        'created_by' => opa_user_id(),
    ), array('%d', '%s', '%s', '%s', '%s', '%d'));
}
