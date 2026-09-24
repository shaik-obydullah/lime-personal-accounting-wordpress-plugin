<?php
if (!defined('ABSPATH')) exit;

function opaac_get_summary() {
    $group = opaac_cache_group();
    $summary = wp_cache_get('summary', $group);
    if (false !== $summary) {
        return $summary;
    }

    $wallets_table = opaac_table('wallets');
    $incomes_table = opaac_table('incomes');
    $expenses_table = opaac_table('expenses');
    $db = opaac_db();

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

function opaac_page_dashboard() {
    $summary = opaac_get_summary();
    $total_income = $summary['total_income'];
    $total_expense = $summary['total_expense'];
    $balance = $summary['balance'];
    $income_count = $summary['income_count'];
    $expense_count = $summary['expense_count'];
    $wallet_count = $summary['wallet_count'];

    include OPAAC_PLUGIN_DIR . 'includes/views/opaac-dashboard.php';
}

function opaac_page_wallets() {
    include OPAAC_PLUGIN_DIR . 'includes/views/opaac-wallets.php';
}

function opaac_page_incomes() {
    include OPAAC_PLUGIN_DIR . 'includes/views/opaac-incomes.php';
}

function opaac_page_expenses() {
    include OPAAC_PLUGIN_DIR . 'includes/views/opaac-expenses.php';
}

function opaac_page_cashbook() {
    include OPAAC_PLUGIN_DIR . 'includes/views/opaac-cashbook.php';
}

function opaac_page_activities() {
    include OPAAC_PLUGIN_DIR . 'includes/views/opaac-activities.php';
}

function opaac_page_settings() {
    include OPAAC_PLUGIN_DIR . 'includes/views/opaac-settings.php';
}
