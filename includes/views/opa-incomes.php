<?php
if (!defined('ABSPATH')) exit;
?>
<div class="opa-wrap">
    <div class="opa-header">
        <h1><?php esc_html_e('Incomes', 'obydullah-personal-accounting'); ?></h1>
        <button class="opa-btn opa-btn-primary" onclick="opaIncomeModal()">+ <?php esc_html_e('Add Income', 'obydullah-personal-accounting'); ?></button>
    </div>
    <div id="opa-incomes-table"></div>
</div>

<div id="opa-income-modal" class="opa-modal" style="display:none">
    <div class="opa-modal-overlay" onclick="opaCloseModal()"></div>
    <div class="opa-modal-content">
        <div class="opa-modal-header">
            <h2 id="opa-income-modal-title"><?php esc_html_e('Add Income', 'obydullah-personal-accounting'); ?></h2>
            <button class="opa-modal-close" onclick="opaCloseModal()">&times;</button>
        </div>
        <form id="opa-income-form" onsubmit="return opaSubmitIncome(event)">
            <input type="hidden" id="opa-income-id" value="">
            <div class="opa-form-group">
                <label><?php esc_html_e('Wallet', 'obydullah-personal-accounting'); ?></label>
                <select id="opa-income-wallet" required>
                    <option value=""><?php esc_html_e('Select wallet', 'obydullah-personal-accounting'); ?></option>
                </select>
            </div>
            <div class="opa-form-group">
                <label><?php esc_html_e('Amount', 'obydullah-personal-accounting'); ?></label>
                <input type="number" step="0.01" id="opa-income-amount" required placeholder="0.00">
            </div>
            <div class="opa-form-group">
                <label><?php esc_html_e('Description', 'obydullah-personal-accounting'); ?></label>
                <textarea id="opa-income-description" required rows="3" placeholder="<?php esc_attr_e('Income description', 'obydullah-personal-accounting'); ?>"></textarea>
            </div>
            <div class="opa-form-group">
                <label><?php esc_html_e('Currency', 'obydullah-personal-accounting'); ?></label>
                <input type="text" id="opa-income-currency" placeholder="<?php esc_attr_e('e.g. USD, BDT', 'obydullah-personal-accounting'); ?>" value="USD">
            </div>
            <div class="opa-form-actions">
                <button type="button" class="opa-btn" onclick="opaCloseModal()"><?php esc_html_e('Cancel', 'obydullah-personal-accounting'); ?></button>
                <button type="submit" class="opa-btn opa-btn-primary"><?php esc_html_e('Save', 'obydullah-personal-accounting'); ?></button>
            </div>
        </form>
    </div>
</div>
