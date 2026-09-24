<?php
if (!defined('ABSPATH')) exit;
?>
<div class="opaac-wrap">
    <div class="opaac-header">
        <h1><?php esc_html_e('Expenses', 'obydullah-personal-accounting'); ?></h1>
        <button class="opaac-btn opaac-btn-primary" onclick="opaacExpenseModal()">+ <?php esc_html_e('Add Expense', 'obydullah-personal-accounting'); ?></button>
    </div>
    <div id="opaac-expenses-table"></div>
</div>

<div id="opaac-expense-modal" class="opaac-modal" style="display:none">
    <div class="opaac-modal-overlay" onclick="opaacCloseModal()"></div>
    <div class="opaac-modal-content">
        <div class="opaac-modal-header">
            <h2 id="opaac-expense-modal-title"><?php esc_html_e('Add Expense', 'obydullah-personal-accounting'); ?></h2>
            <button class="opaac-modal-close" onclick="opaacCloseModal()">&times;</button>
        </div>
        <form id="opaac-expense-form" onsubmit="return opaacSubmitExpense(event)">
            <input type="hidden" id="opaac-expense-id" value="">
            <div class="opaac-form-group">
                <label><?php esc_html_e('Wallet', 'obydullah-personal-accounting'); ?></label>
                <select id="opaac-expense-wallet" required>
                    <option value=""><?php esc_html_e('Select wallet', 'obydullah-personal-accounting'); ?></option>
                </select>
            </div>
            <div class="opaac-form-group">
                <label><?php esc_html_e('Amount', 'obydullah-personal-accounting'); ?></label>
                <input type="number" step="0.01" id="opaac-expense-amount" required placeholder="0.00">
            </div>
            <div class="opaac-form-group">
                <label><?php esc_html_e('Description', 'obydullah-personal-accounting'); ?></label>
                <textarea id="opaac-expense-description" required rows="3" placeholder="<?php esc_attr_e('Expense description', 'obydullah-personal-accounting'); ?>"></textarea>
            </div>
            <div class="opaac-form-group">
                <label><?php esc_html_e('Currency', 'obydullah-personal-accounting'); ?></label>
                <input type="text" id="opaac-expense-currency" placeholder="<?php esc_attr_e('e.g. USD, BDT', 'obydullah-personal-accounting'); ?>" value="USD">
            </div>
            <div class="opaac-form-actions">
                <button type="button" class="opaac-btn" onclick="opaacCloseModal()"><?php esc_html_e('Cancel', 'obydullah-personal-accounting'); ?></button>
                <button type="submit" class="opaac-btn opaac-btn-primary"><?php esc_html_e('Save', 'obydullah-personal-accounting'); ?></button>
            </div>
        </form>
    </div>
</div>
