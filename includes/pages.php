<?php
if (!defined('ABSPATH')) exit;

function lpa_page_dashboard() {
    $wallets_table = lpa_table('wallets');
    $incomes_table = lpa_table('incomes');
    $expenses_table = lpa_table('expenses');
    $db = lpa_db();

    $total_income = (float) $db->get_var("SELECT COALESCE(SUM(amount),0) FROM $incomes_table WHERE deleted_at IS NULL");
    $total_expense = (float) $db->get_var("SELECT COALESCE(SUM(amount),0) FROM $expenses_table WHERE deleted_at IS NULL");
    $balance = $total_income - $total_expense;
    $income_count = (int) $db->get_var("SELECT COUNT(*) FROM $incomes_table WHERE deleted_at IS NULL");
    $expense_count = (int) $db->get_var("SELECT COUNT(*) FROM $expenses_table WHERE deleted_at IS NULL");
    $wallet_count = (int) $db->get_var("SELECT COUNT(*) FROM $wallets_table WHERE deleted_at IS NULL");

    include LPA_PLUGIN_DIR . 'includes/views/dashboard.php';
}

function lpa_page_wallets() {
    include LPA_PLUGIN_DIR . 'includes/views/wallets.php';
}

function lpa_page_incomes() {
    include LPA_PLUGIN_DIR . 'includes/views/incomes.php';
}

function lpa_page_expenses() {
    include LPA_PLUGIN_DIR . 'includes/views/expenses.php';
}

function lpa_page_cashbook() {
    include LPA_PLUGIN_DIR . 'includes/views/cashbook.php';
}

function lpa_page_activities() {
    include LPA_PLUGIN_DIR . 'includes/views/activities.php';
}

function lpa_page_settings() {
    include LPA_PLUGIN_DIR . 'includes/views/settings.php';
}
