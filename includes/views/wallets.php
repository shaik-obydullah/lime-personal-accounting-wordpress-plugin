<?php if (!defined('ABSPATH')) exit; ?>
<div class="lpa-wrap">
    <div class="lpa-header">
        <h1>Wallets</h1>
        <button class="lpa-btn lpa-btn-primary" onclick="lpaWalletModal()">+ Add Wallet</button>
    </div>
    <div id="lpa-wallets-table"></div>
</div>

<div id="lpa-wallet-modal" class="lpa-modal" style="display:none">
    <div class="lpa-modal-overlay" onclick="lpaCloseModal()"></div>
    <div class="lpa-modal-content">
        <div class="lpa-modal-header">
            <h2 id="lpa-wallet-modal-title">Add Wallet</h2>
            <button class="lpa-modal-close" onclick="lpaCloseModal()">&times;</button>
        </div>
        <form id="lpa-wallet-form" onsubmit="return lpaSubmitWallet(event)">
            <input type="hidden" id="lpa-wallet-id" value="">
            <div class="lpa-form-group">
                <label>Name</label>
                <input type="text" id="lpa-wallet-name" required placeholder="e.g. Salary, Food, Rent">
            </div>
            <div class="lpa-form-group">
                <label>Category</label>
                <select id="lpa-wallet-category" required>
                    <option value="">Select category</option>
                    <option value="income">Income</option>
                    <option value="expense">Expense</option>
                </select>
            </div>
            <div class="lpa-form-actions">
                <button type="button" class="lpa-btn" onclick="lpaCloseModal()">Cancel</button>
                <button type="submit" class="lpa-btn lpa-btn-primary">Save</button>
            </div>
        </form>
    </div>
</div>
