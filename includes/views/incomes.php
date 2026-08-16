<?php if (!defined('ABSPATH')) exit; ?>
<div class="lpa-wrap">
    <div class="lpa-header">
        <h1>Incomes</h1>
        <button class="lpa-btn lpa-btn-primary" onclick="lpaIncomeModal()">+ Add Income</button>
    </div>
    <div id="lpa-incomes-table"></div>
</div>

<div id="lpa-income-modal" class="lpa-modal" style="display:none">
    <div class="lpa-modal-overlay" onclick="lpaCloseModal()"></div>
    <div class="lpa-modal-content">
        <div class="lpa-modal-header">
            <h2 id="lpa-income-modal-title">Add Income</h2>
            <button class="lpa-modal-close" onclick="lpaCloseModal()">&times;</button>
        </div>
        <form id="lpa-income-form" onsubmit="return lpaSubmitIncome(event)">
            <input type="hidden" id="lpa-income-id" value="">
            <div class="lpa-form-group">
                <label>Wallet</label>
                <select id="lpa-income-wallet" required>
                    <option value="">Select wallet</option>
                </select>
            </div>
            <div class="lpa-form-group">
                <label>Amount</label>
                <input type="number" step="0.01" id="lpa-income-amount" required placeholder="0.00">
            </div>
            <div class="lpa-form-group">
                <label>Description</label>
                <textarea id="lpa-income-description" required rows="3" placeholder="Income description"></textarea>
            </div>
            <div class="lpa-form-group">
                <label>Currency</label>
                <input type="text" id="lpa-income-currency" placeholder="e.g. USD, BDT" value="USD">
            </div>
            <div class="lpa-form-actions">
                <button type="button" class="lpa-btn" onclick="lpaCloseModal()">Cancel</button>
                <button type="submit" class="lpa-btn lpa-btn-primary">Save</button>
            </div>
        </form>
    </div>
</div>
