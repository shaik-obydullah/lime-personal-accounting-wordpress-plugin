<?php
if (!defined('ABSPATH')) exit;

function opa_get_summary() {
    $group = opa_cache_group();
    $summary = wp_cache_get('summary', $group);
    if (false !== $summary) {
        return $summary;
    }

    $wallets_table = opa_table('wallets');
    $incomes_table = opa_table('incomes');
    $expenses_table = opa_table('expenses');
    $db = opa_db();

    $total_income = (float) $db->get_var(
        $db->prepare(
            "SELECT COALESCE(SUM(amount),0) FROM {$incomes_table} WHERE 1 = %d AND deleted_at IS NULL",
            1
        )
    );
    $total_expense = (float) $db->get_var(
        $db->prepare(
            "SELECT COALESCE(SUM(amount),0) FROM {$expenses_table} WHERE 1 = %d AND deleted_at IS NULL",
            1
        )
    );
    $income_count = (int) $db->get_var(
        $db->prepare(
            "SELECT COUNT(*) FROM {$incomes_table} WHERE 1 = %d AND deleted_at IS NULL",
            1
        )
    );
    $expense_count = (int) $db->get_var(
        $db->prepare(
            "SELECT COUNT(*) FROM {$expenses_table} WHERE 1 = %d AND deleted_at IS NULL",
            1
        )
    );
    $wallet_count = (int) $db->get_var(
        $db->prepare(
            "SELECT COUNT(*) FROM {$wallets_table} WHERE 1 = %d AND deleted_at IS NULL",
            1
        )
    );

    $summary = array(
        'total_income'  => $total_income,
        'total_expense' => $total_expense,
        'balance'       => $total_income - $total_expense,
        'income_count'  => $income_count,
        'expense_count' => $expense_count,
        'wallet_count'  => $wallet_count,
    );

    wp_cache_set('summary', $summary, $group, 300);
    return $summary;
}

function opa_page_dashboard() {
    $summary = opa_get_summary();
    $total_income = $summary['total_income'];
    $total_expense = $summary['total_expense'];
    $balance = $summary['balance'];
    $income_count = $summary['income_count'];
    $expense_count = $summary['expense_count'];
    $wallet_count = $summary['wallet_count'];

    include OPA_PLUGIN_DIR . 'includes/views/opa-dashboard.php';
}

function opa_page_wallets() {
    include OPA_PLUGIN_DIR . 'includes/views/opa-wallets.php';
}

function opa_page_incomes() {
    include OPA_PLUGIN_DIR . 'includes/views/opa-incomes.php';
}

function opa_page_expenses() {
    include OPA_PLUGIN_DIR . 'includes/views/opa-expenses.php';
}

function opa_page_cashbook() {
    include OPA_PLUGIN_DIR . 'includes/views/opa-cashbook.php';
}

function opa_page_activities() {
    include OPA_PLUGIN_DIR . 'includes/views/opa-activities.php';
}

function opa_page_settings() {
    include OPA_PLUGIN_DIR . 'includes/views/opa-settings.php';
}
