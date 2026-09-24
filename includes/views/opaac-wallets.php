<?php
if (!defined('ABSPATH')) exit;
?>
<div class="opaac-wrap">
    <div class="opaac-header">
        <h1><?php esc_html_e('Wallets', 'obydullah-personal-accounting'); ?></h1>
        <button class="opaac-btn opaac-btn-primary" onclick="opaacWalletModal()">+ <?php esc_html_e('Add Wallet', 'obydullah-personal-accounting'); ?></button>
    </div>
    <div id="opaac-wallets-table"></div>
</div>

<div id="opaac-wallet-modal" class="opaac-modal" style="display:none">
    <div class="opaac-modal-overlay" onclick="opaacCloseModal()"></div>
    <div class="opaac-modal-content">
        <div class="opaac-modal-header">
            <h2 id="opaac-wallet-modal-title"><?php esc_html_e('Add Wallet', 'obydullah-personal-accounting'); ?></h2>
            <button class="opaac-modal-close" onclick="opaacCloseModal()">&times;</button>
        </div>
        <form id="opaac-wallet-form" onsubmit="return opaacSubmitWallet(event)">
            <input type="hidden" id="opaac-wallet-id" value="">
            <div class="opaac-form-group">
                <label><?php esc_html_e('Name', 'obydullah-personal-accounting'); ?></label>
                <input type="text" id="opaac-wallet-name" required placeholder="<?php esc_attr_e('e.g. Salary, Food, Rent', 'obydullah-personal-accounting'); ?>">
            </div>
            <div class="opaac-form-group">
                <label><?php esc_html_e('Category', 'obydullah-personal-accounting'); ?></label>
                <select id="opaac-wallet-category" required>
                    <option value=""><?php esc_html_e('Select category', 'obydullah-personal-accounting'); ?></option>
                    <option value="income"><?php esc_html_e('Income', 'obydullah-personal-accounting'); ?></option>
                    <option value="expense"><?php esc_html_e('Expense', 'obydullah-personal-accounting'); ?></option>
                </select>
            </div>
            <div class="opaac-form-actions">
                <button type="button" class="opaac-btn" onclick="opaacCloseModal()"><?php esc_html_e('Cancel', 'obydullah-personal-accounting'); ?></button>
                <button type="submit" class="opaac-btn opaac-btn-primary"><?php esc_html_e('Save', 'obydullah-personal-accounting'); ?></button>
            </div>
        </form>
    </div>
</div>
