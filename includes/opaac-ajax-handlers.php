<?php
if (!defined('ABSPATH')) exit;

function opaac_ajax_router() {
    check_ajax_referer('opaac_nonce', 'nonce');

    if (!current_user_can('manage_options')) {
        wp_send_json_error(array('message' => __('Unauthorized', 'obydullah-personal-accounting')));
    }

    $action = sanitize_text_field( wp_unslash( $_POST['opaac_action'] ?? $_GET['opaac_action'] ?? '' ) );
    $data = wp_unslash( $_POST['data'] ?? array() ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- each field is sanitized by the handler.

    switch ($action) {
        case 'get_wallets':
            opaac_get_wallets();
            break;
        case 'save_wallet':
            opaac_save_wallet($data);
            break;
        case 'delete_wallet':
            opaac_delete_wallet($data);
            break;
        case 'get_incomes':
            opaac_get_incomes();
            break;
        case 'save_income':
            opaac_save_income($data);
            break;
        case 'delete_income':
            opaac_delete_income($data);
            break;
        case 'get_expenses':
            opaac_get_expenses();
            break;
        case 'save_expense':
            opaac_save_expense($data);
            break;
        case 'delete_expense':
            opaac_delete_expense($data);
            break;
        case 'get_cashbook':
            opaac_get_cashbook();
            break;
        case 'get_activities':
            opaac_get_activities();
            break;
        case 'get_settings':
            opaac_get_settings();
            break;
        case 'save_settings':
            opaac_save_settings($data);
            break;
        case 'reset_data':
            opaac_reset_data();
            break;
        case 'export_csv':
            opaac_export_csv();
            break;
        default:
            wp_send_json_error(array('message' => __('Unknown action', 'obydullah-personal-accounting')));
    }
}

function opaac_get_wallets() {
    $db = opaac_db();
    $table = opaac_table('wallets');
    $results = $db->get_results(
        $db->prepare(
            "SELECT * FROM {$table} WHERE 1 = %d AND deleted_at IS NULL ORDER BY id DESC",
            1
        ),
        ARRAY_A
    );
    wp_send_json_success($results);
}

function opaac_save_wallet($data) {
    $db = opaac_db();
    $table = opaac_table('wallets');
    $id = isset($data['id']) ? absint($data['id']) : 0;
    $name = sanitize_text_field($data['name'] ?? '');
    $category = sanitize_text_field($data['category'] ?? '');
    $now = opaac_now();
    $user = opaac_user_id();

    if ($id > 0) {
        $db->update(
            $table,
            [
                'name'      => $name,
                'category'  => $category,
                'updated_at' => $now,
                'updated_by' => $user,
            ],
            ['id' => $id],
            ['%s', '%s', '%s', '%d'],
            ['%d']
        );
        opaac_flush_cache();
        wp_send_json_success(array('message' => __('Wallet updated', 'obydullah-personal-accounting')));
    } else {
        $db->insert($table, array(
            'name' => $name,
            'category' => $category,
            'created_at' => $now,
            'created_by' => $user,
        ), array('%s', '%s', '%s', '%d'));
        opaac_flush_cache();
        wp_send_json_success(array('message' => __('Wallet created', 'obydullah-personal-accounting'), 'id' => $db->insert_id));
    }
}

function opaac_delete_wallet($data) {
    $db = opaac_db();
    $table = opaac_table('wallets');
    $id = absint($data['id'] ?? 0);
    $db->delete(
        $table,
        ['id' => $id],
        ['%d']
    );
    opaac_flush_cache();
    wp_send_json_success(array('message' => __('Wallet deleted', 'obydullah-personal-accounting')));
}

function opaac_get_incomes() {
    $db = opaac_db();
    $table = opaac_table('incomes');
    $wallet_table = opaac_table('wallets');
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

function opaac_save_income($data) {
    $db = opaac_db();
    $table = opaac_table('incomes');
    $cashbook_table = opaac_table('cashbook');
    $id = isset($data['id']) ? absint($data['id']) : 0;
    $wallet_id = absint($data['wallet_id'] ?? 0);
    $amount = floatval($data['amount'] ?? 0);
    $description = sanitize_textarea_field($data['description'] ?? '');
    $currency = sanitize_text_field($data['currency'] ?? '');
    $now = opaac_now();
    $user = opaac_user_id();

    if ($id > 0) {
        $db->update(
            $table,
            [
                'fk_wallet_id' => $wallet_id,
                'amount'       => $amount,
                'description'  => $description,
                'currency'     => $currency,
                'updated_at'   => $now,
                'updated_by'   => $user,
            ],
            ['id' => $id],
            ['%d', '%f', '%s', '%s', '%s', '%d'],
            ['%d']
        );
        opaac_flush_cache();
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
        opaac_flush_cache();

        $db->insert($cashbook_table, array(
            'in_amount' => $amount,
            'fk_reference_id' => $income_id,
            'reference_type' => 'income',
            'created_at' => $now,
            'created_by' => $user,
        ), array('%f', '%d', '%s', '%s', '%d'));

        /* translators: %s: income amount. */
        opaac_log_activity('success', sprintf(__('Income Added: %s', 'obydullah-personal-accounting'), $amount));

        wp_send_json_success(array('message' => __('Income created', 'obydullah-personal-accounting'), 'id' => $income_id));
    }
}

function opaac_delete_income($data) {
    $db = opaac_db();
    $table = opaac_table('incomes');
    $cashbook_table = opaac_table('cashbook');
    $id = absint($data['id'] ?? 0);

    $db->delete(
        $table,
        ['id' => $id],
        ['%d']
    );
    $db->delete(
        $cashbook_table,
        ['fk_reference_id' => $id, 'reference_type' => 'income'],
        ['%d', '%s']
    );
    opaac_flush_cache();

    wp_send_json_success(array('message' => __('Income deleted', 'obydullah-personal-accounting')));
}

function opaac_get_expenses() {
    $db = opaac_db();
    $table = opaac_table('expenses');
    $wallet_table = opaac_table('wallets');
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

function opaac_save_expense($data) {
    $db = opaac_db();
    $table = opaac_table('expenses');
    $cashbook_table = opaac_table('cashbook');
    $id = isset($data['id']) ? absint($data['id']) : 0;
    $wallet_id = absint($data['wallet_id'] ?? 0);
    $amount = floatval($data['amount'] ?? 0);
    $description = sanitize_textarea_field($data['description'] ?? '');
    $currency = sanitize_text_field($data['currency'] ?? '');
    $now = opaac_now();
    $user = opaac_user_id();

    if ($id > 0) {
        $db->update(
            $table,
            [
                'fk_wallet_id' => $wallet_id,
                'amount'       => $amount,
                'description'  => $description,
                'currency'     => $currency,
                'updated_at'   => $now,
                'updated_by'   => $user,
            ],
            ['id' => $id],
            ['%d', '%f', '%s', '%s', '%s', '%d'],
            ['%d']
        );
        opaac_flush_cache();
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
        opaac_flush_cache();

        $db->insert($cashbook_table, array(
            'out_amount' => $amount,
            'fk_reference_id' => $expense_id,
            'reference_type' => 'expense',
            'created_at' => $now,
            'created_by' => $user,
        ), array('%f', '%d', '%s', '%s', '%d'));

        /* translators: %s: expense amount. */
        opaac_log_activity('warning', sprintf(__('Expense Added: %s', 'obydullah-personal-accounting'), $amount));

        wp_send_json_success(array('message' => __('Expense created', 'obydullah-personal-accounting'), 'id' => $expense_id));
    }
}

function opaac_delete_expense($data) {
    $db = opaac_db();
    $table = opaac_table('expenses');
    $cashbook_table = opaac_table('cashbook');
    $id = absint($data['id'] ?? 0);

    $db->delete(
        $table,
        ['id' => $id],
        ['%d']
    );
    $db->delete(
        $cashbook_table,
        ['fk_reference_id' => $id, 'reference_type' => 'expense'],
        ['%d', '%s']
    );
    opaac_flush_cache();

    wp_send_json_success(array('message' => __('Expense deleted', 'obydullah-personal-accounting')));
}

function opaac_get_cashbook() {
    $db = opaac_db();
    $table = opaac_table('cashbook');
    $results = $db->get_results(
        $db->prepare(
            "SELECT * FROM {$table} WHERE 1 = %d AND deleted_at IS NULL ORDER BY id DESC",
            1
        ),
        ARRAY_A
    );
    wp_send_json_success($results);
}

function opaac_get_activities() {
    $db = opaac_db();
    $table = opaac_table('activities');
    $results = $db->get_results(
        $db->prepare(
            "SELECT * FROM {$table} WHERE 1 = %d AND deleted_at IS NULL ORDER BY id DESC LIMIT 50",
            1
        ),
        ARRAY_A
    );
    wp_send_json_success($results);
}

function opaac_get_settings() {
    $group = opaac_cache_group();
    $results = wp_cache_get('settings', $group);
    if (false === $results) {
        $db = opaac_db();
        $table = opaac_table('configurations');
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

function opaac_get_setting($name, $default = '') {
    $db = opaac_db();
    $table = opaac_table('configurations');
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

function opaac_set_setting($name, $value) {
    $db = opaac_db();
    $table = opaac_table('configurations');
    $name = sanitize_key($name);
    $setting = wp_json_encode($value);
    $now = opaac_now();
    $user = opaac_user_id();

    $id = (int) $db->get_var(
        $db->prepare(
            "SELECT id FROM {$table} WHERE name = %s AND deleted_at IS NULL LIMIT 1",
            $name
        )
    );

    if ($id > 0) {
        $db->update(
            $table,
            [
                'setting'    => $setting,
                'updated_at' => $now,
                'updated_by' => $user,
            ],
            ['id' => $id],
            ['%s', '%s', '%d'],
            ['%d']
        );
    } else {
        $db->insert($table, array(
            'name'       => $name,
            'setting'    => $setting,
            'created_at' => $now,
            'created_by' => $user,
        ), array('%s', '%s', '%s', '%d'));
    }

    wp_cache_delete('settings', opaac_cache_group());
}

function opaac_save_settings($data) {
    $currency = strtoupper(sanitize_text_field($data['default_currency'] ?? ''));
    if ('' === $currency) {
        $currency = 'USD';
    }
    $currency_symbol = sanitize_text_field($data['currency_symbol'] ?? '');
    $page_size = absint($data['page_size'] ?? 10);
    $page_size = max(5, min(100, $page_size));
    $default_wallet_id = absint($data['default_wallet_id'] ?? 0);

    opaac_set_setting('default_currency', $currency);
    opaac_set_setting('currency_symbol', $currency_symbol);
    opaac_set_setting('page_size', $page_size);
    opaac_set_setting('default_wallet_id', $default_wallet_id);

    $db = opaac_db();
    if (!empty($db->last_error)) {
        wp_send_json_error(array('message' => $db->last_error));
    }

    $message = __('Settings saved', 'obydullah-personal-accounting');
    opaac_log_activity('success', $message);
    wp_send_json_success(array('message' => $message));
}

function opaac_reset_data() {
    $db = opaac_db();
    $tables = array('wallets', 'incomes', 'expenses', 'cashbook', 'activities', 'configurations');
    foreach ($tables as $name) {
        $table = opaac_table($name);
        $db->query(
            $db->prepare(
                "DELETE FROM `{$table}` WHERE 1 = %d",
                1
            )
        );
    }
    opaac_flush_cache();

    /* translators: %s: current date. */
    $message = sprintf(__('All data wiped on %s', 'obydullah-personal-accounting'), opaac_now());
    opaac_log_activity('warning', $message);
    wp_send_json_success(array('message' => __('All data wiped', 'obydullah-personal-accounting')));
}

function opaac_csv_safe_value($value) {
    $value = (string) $value;
    if ('' !== $value && in_array($value[0], array('=', '+', '-', '@'), true)) {
        return "'" . $value;
    }
    return $value;
}

function opaac_export_csv() {
    $db = opaac_db();
    $wallet_table = opaac_table('wallets');
    $incomes_table = opaac_table('incomes');
    $expenses_table = opaac_table('expenses');

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
    header('Content-Disposition: attachment; filename="opaac-accounting-export-' . wp_date('Y-m-d') . '.csv"');

    $out = fopen('php://output', 'w');
    fputcsv($out, array(__('Type', 'obydullah-personal-accounting'), __('Wallet', 'obydullah-personal-accounting'), __('Amount', 'obydullah-personal-accounting'), __('Currency', 'obydullah-personal-accounting'), __('Description', 'obydullah-personal-accounting'), __('Date', 'obydullah-personal-accounting')));
    foreach ($rows as $row) {
        fputcsv($out, array(
            opaac_csv_safe_value($row['type']),
            opaac_csv_safe_value($row['wallet_name']),
            $row['amount'],
            opaac_csv_safe_value($row['currency']),
            opaac_csv_safe_value($row['description']),
            $row['entry_date'],
        ));
    }
    wp_die();
}

function opaac_log_activity($type, $name) {
    $db = opaac_db();
    $table = opaac_table('activities');
    $ip_address = filter_var(wp_unslash($_SERVER['REMOTE_ADDR'] ?? ''), FILTER_VALIDATE_IP);
    $db->insert($table, array(
        'fk_admin_id' => opaac_user_id(),
        'type' => sanitize_text_field($type),
        'name' => sanitize_text_field($name),
        'ip_address' => $ip_address ? $ip_address : '',
        'created_at' => opaac_now(),
        'created_by' => opaac_user_id(),
    ), array('%d', '%s', '%s', '%s', '%s', '%d'));
}
