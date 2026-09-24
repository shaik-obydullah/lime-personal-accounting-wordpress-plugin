<?php
if (!defined('ABSPATH')) exit;
?>
<div class="opaac-wrap">
    <h1><?php esc_html_e('Settings', 'obydullah-personal-accounting'); ?></h1>

    <form id="opaac-settings-form" onsubmit="return opaacSaveSettings(event)">
        <div class="opaac-settings-card">
            <h2><?php esc_html_e('General', 'obydullah-personal-accounting'); ?></h2>

            <div class="opaac-form-group">
                <label for="opaac-settings-currency"><?php esc_html_e('Default Currency', 'obydullah-personal-accounting'); ?></label>
                <select id="opaac-settings-currency">
                    <option value=""><?php esc_html_e('— Select currency —', 'obydullah-personal-accounting'); ?></option>
                    <option value="USD"><?php esc_html_e('USD — US Dollar', 'obydullah-personal-accounting'); ?></option>
                    <option value="EUR"><?php esc_html_e('EUR — Euro', 'obydullah-personal-accounting'); ?></option>
                    <option value="GBP"><?php esc_html_e('GBP — British Pound', 'obydullah-personal-accounting'); ?></option>
                    <option value="BDT"><?php esc_html_e('BDT — Bangladeshi Taka', 'obydullah-personal-accounting'); ?></option>
                    <option value="INR"><?php esc_html_e('INR — Indian Rupee', 'obydullah-personal-accounting'); ?></option>
                    <option value="PKR"><?php esc_html_e('PKR — Pakistani Rupee', 'obydullah-personal-accounting'); ?></option>
                    <option value="CAD"><?php esc_html_e('CAD — Canadian Dollar', 'obydullah-personal-accounting'); ?></option>
                    <option value="AUD"><?php esc_html_e('AUD — Australian Dollar', 'obydullah-personal-accounting'); ?></option>
                    <option value="JPY"><?php esc_html_e('JPY — Japanese Yen', 'obydullah-personal-accounting'); ?></option>
                    <option value="CNY"><?php esc_html_e('CNY — Chinese Yuan', 'obydullah-personal-accounting'); ?></option>
                    <option value="AED"><?php esc_html_e('AED — UAE Dirham', 'obydullah-personal-accounting'); ?></option>
                    <option value="SAR"><?php esc_html_e('SAR — Saudi Riyal', 'obydullah-personal-accounting'); ?></option>
                    <option value="MYR"><?php esc_html_e('MYR — Malaysian Ringgit', 'obydullah-personal-accounting'); ?></option>
                    <option value="SGD"><?php esc_html_e('SGD — Singapore Dollar', 'obydullah-personal-accounting'); ?></option>
                    <option value="NZD"><?php esc_html_e('NZD — New Zealand Dollar', 'obydullah-personal-accounting'); ?></option>
                </select>
            </div>

            <div class="opaac-form-group">
                <label for="opaac-settings-symbol"><?php esc_html_e('Currency icon / symbol', 'obydullah-personal-accounting'); ?></label>
                <input type="text" id="opaac-settings-symbol" placeholder="<?php esc_attr_e('e.g. $, €, ৳, £, ¥', 'obydullah-personal-accounting'); ?>" value="">
            </div>

            <div class="opaac-form-group">
                <label for="opaac-settings-page-size"><?php esc_html_e('Table page size', 'obydullah-personal-accounting'); ?></label>
                <input type="number" id="opaac-settings-page-size" min="5" max="100" step="1" value="10">
                <p class="opaac-settings-note"><?php esc_html_e('Number of rows to show per page on the Wallets, Incomes, Expenses, Cashbook and Activities tables (5–100).', 'obydullah-personal-accounting'); ?></p>
            </div>

            <div class="opaac-form-group">
                <label for="opaac-settings-default-wallet"><?php esc_html_e('Default wallet', 'obydullah-personal-accounting'); ?></label>
                <select id="opaac-settings-default-wallet">
                    <option value=""><?php esc_html_e('— None —', 'obydullah-personal-accounting'); ?></option>
                </select>
                <p class="opaac-settings-note opaac-inline-loading" id="opaac-settings-wallet-loading" style="display:none;">
                    <span class="opaac-spinner"></span>
                    <?php esc_html_e('Loading wallets...', 'obydullah-personal-accounting'); ?>
                </p>
            </div>

            <div class="opaac-form-actions">
                <button type="submit" class="opaac-btn opaac-btn-primary"><?php esc_html_e('Save Settings', 'obydullah-personal-accounting'); ?></button>
            </div>
        </div>

        <div class="opaac-settings-card opaac-danger-zone">
            <h2><?php esc_html_e('Data', 'obydullah-personal-accounting'); ?></h2>

            <div class="opaac-settings-actions">
                <button type="button" class="opaac-btn" id="opaac-export-csv" onclick="opaacExportCsv()"><?php esc_html_e('Export to CSV', 'obydullah-personal-accounting'); ?></button>
                <button type="button" class="opaac-btn opaac-btn-danger" id="opaac-reset-data" onclick="opaacResetData()"><?php esc_html_e('Reset / Wipe all data', 'obydullah-personal-accounting'); ?></button>
            </div>
            <p class="opaac-settings-note"><?php esc_html_e('Export downloads your incomes and expenses as a CSV file. Reset permanently deletes all wallets, transactions, cashbook entries, activities, and saved settings.', 'obydullah-personal-accounting'); ?></p>
        </div>
    </form>
</div>