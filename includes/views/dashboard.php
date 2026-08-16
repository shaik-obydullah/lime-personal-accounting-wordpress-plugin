<?php if (!defined('ABSPATH')) exit; ?>
<div class="lpa-wrap">
    <h1>Dashboard</h1>
    <div class="lpa-stats-grid">
        <div class="lpa-stat-card lpa-stat-income">
            <div class="lpa-stat-icon">&#9650;</div>
            <div class="lpa-stat-info">
                <span class="lpa-stat-value"><?php echo number_format($total_income, 2); ?></span>
                <span class="lpa-stat-label">Total Income</span>
                <span class="lpa-stat-count"><?php echo $income_count; ?> transactions</span>
            </div>
        </div>
        <div class="lpa-stat-card lpa-stat-expense">
            <div class="lpa-stat-icon">&#9660;</div>
            <div class="lpa-stat-info">
                <span class="lpa-stat-value"><?php echo number_format($total_expense, 2); ?></span>
                <span class="lpa-stat-label">Total Expense</span>
                <span class="lpa-stat-count"><?php echo $expense_count; ?> transactions</span>
            </div>
        </div>
        <div class="lpa-stat-card lpa-stat-balance">
            <div class="lpa-stat-icon">&#9670;</div>
            <div class="lpa-stat-info">
                <span class="lpa-stat-value"><?php echo number_format($balance, 2); ?></span>
                <span class="lpa-stat-label">Balance</span>
                <span class="lpa-stat-count"><?php echo $wallet_count; ?> wallets</span>
            </div>
        </div>
    </div>
</div>
