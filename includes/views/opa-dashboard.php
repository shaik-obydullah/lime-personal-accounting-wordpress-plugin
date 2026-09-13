<?php
if (!defined('ABSPATH')) exit;
?>
<div class="opa-wrap">
    <h1><?php esc_html_e('Dashboard', 'obydullah-personal-accounting'); ?></h1>
    <div class="opa-stats-grid">
        <div class="opa-stat-card opa-stat-income">
            <div class="opa-stat-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><path d="M22 7l-8.5 8.5-5-5L2 17"/><path d="M16 7h6v6"/></svg>
            </div>
            <div class="opa-stat-info">
                <span class="opa-stat-value"><?php echo esc_html(number_format_i18n($total_income, 2)); ?></span>
                <span class="opa-stat-label"><?php esc_html_e('Total Income', 'obydullah-personal-accounting'); ?></span>
                <span class="opa-stat-count"><?php echo esc_html(sprintf(/* translators: %d: number of transactions. */ _n('%d transaction', '%d transactions', $income_count, 'obydullah-personal-accounting'), $income_count)); ?></span>
            </div>
        </div>
        <div class="opa-stat-card opa-stat-expense">
            <div class="opa-stat-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><path d="M22 17l-8.5-8.5-5 5L2 7"/><path d="M16 17h6v-6"/></svg>
            </div>
            <div class="opa-stat-info">
                <span class="opa-stat-value"><?php echo esc_html(number_format_i18n($total_expense, 2)); ?></span>
                <span class="opa-stat-label"><?php esc_html_e('Total Expense', 'obydullah-personal-accounting'); ?></span>
                <span class="opa-stat-count"><?php echo esc_html(sprintf(/* translators: %d: number of transactions. */ _n('%d transaction', '%d transactions', $expense_count, 'obydullah-personal-accounting'), $expense_count)); ?></span>
            </div>
        </div>
        <div class="opa-stat-card opa-stat-balance">
            <div class="opa-stat-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><line x1="3" x2="21" y1="22" y2="22"/><line x1="6" x2="6" y1="18" y2="11"/><line x1="10" x2="10" y1="18" y2="11"/><line x1="14" x2="14" y1="18" y2="11"/><line x1="18" x2="18" y1="18" y2="11"/><polygon points="12 2 20 7 4 7"/></svg>
            </div>
            <div class="opa-stat-info">
                <span class="opa-stat-value"><?php echo esc_html(number_format_i18n($balance, 2)); ?></span>
                <span class="opa-stat-label"><?php esc_html_e('Balance', 'obydullah-personal-accounting'); ?></span>
                <span class="opa-stat-count"><?php echo esc_html(sprintf(/* translators: %d: number of wallets. */ _n('%d wallet', '%d wallets', $wallet_count, 'obydullah-personal-accounting'), $wallet_count)); ?></span>
            </div>
        </div>
    </div>
</div>
