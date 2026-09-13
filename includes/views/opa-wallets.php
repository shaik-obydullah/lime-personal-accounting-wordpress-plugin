<?php
if (!defined('ABSPATH')) exit;
?>
<div class="opa-wrap">
    <div class="opa-header">
        <h1><?php esc_html_e('Wallets', 'obydullah-personal-accounting'); ?></h1>
        <button class="opa-btn opa-btn-primary" onclick="opaWalletModal()">+ <?php esc_html_e('Add Wallet', 'obydullah-personal-accounting'); ?></button>
    </div>
    <div id="opa-wallets-table"></div>
</div>

<div id="opa-wallet-modal" class="opa-modal" style="display:none">
    <div class="opa-modal-overlay" onclick="opaCloseModal()"></div>
    <div class="opa-modal-content">
        <div class="opa-modal-header">
            <h2 id="opa-wallet-modal-title"><?php esc_html_e('Add Wallet', 'obydullah-personal-accounting'); ?></h2>
            <button class="opa-modal-close" onclick="opaCloseModal()">&times;</button>
        </div>
        <form id="opa-wallet-form" onsubmit="return opaSubmitWallet(event)">
            <input type="hidden" id="opa-wallet-id" value="">
            <div class="opa-form-group">
                <label><?php esc_html_e('Name', 'obydullah-personal-accounting'); ?></label>
                <input type="text" id="opa-wallet-name" required placeholder="<?php esc_attr_e('e.g. Salary, Food, Rent', 'obydullah-personal-accounting'); ?>">
            </div>
            <div class="opa-form-group">
                <label><?php esc_html_e('Category', 'obydullah-personal-accounting'); ?></label>
                <select id="opa-wallet-category" required>
                    <option value=""><?php esc_html_e('Select category', 'obydullah-personal-accounting'); ?></option>
                    <option value="income"><?php esc_html_e('Income', 'obydullah-personal-accounting'); ?></option>
                    <option value="expense"><?php esc_html_e('Expense', 'obydullah-personal-accounting'); ?></option>
                </select>
            </div>
            <div class="opa-form-actions">
                <button type="button" class="opa-btn" onclick="opaCloseModal()"><?php esc_html_e('Cancel', 'obydullah-personal-accounting'); ?></button>
                <button type="submit" class="opa-btn opa-btn-primary"><?php esc_html_e('Save', 'obydullah-personal-accounting'); ?></button>
            </div>
        </form>
    </div>
</div>
