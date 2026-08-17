jQuery(document).ready(function($) {

    // --- Helper ---
    function lpaPost(action, data, callback) {
        $.post(lpaAjax.ajaxurl, {
            action: 'lpa_action',
            nonce: lpaAjax.nonce,
            lpa_action: action,
            data: data || {}
        }, function(res) {
            if (callback) callback(res);
        }).fail(function() {
            lpaToast('Request failed', 'error');
        });
    }

    function lpaToast(msg, type) {
        var toast = $('<div class="lpa-toast lpa-toast-' + (type || 'success') + '">' + msg + '</div>');
        $('body').append(toast);
        setTimeout(function() { toast.addClass('lpa-toast-show'); }, 10);
        setTimeout(function() { toast.removeClass('lpa-toast-show'); setTimeout(function() { toast.remove(); }, 300); }, 3000);
    }

    function lpaFormatDate(d) {
        if (!d) return '-';
        var dt = new Date(d);
        return dt.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' });
    }

    function lpaFormatMoney(amt) {
        if (!amt) return '0.00';
        return parseFloat(amt).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
    }

    // --- WALLETS ---
    window.lpaWalletModal = function(id) {
        $('#lpa-wallet-id').val('');
        $('#lpa-wallet-name').val('');
        $('#lpa-wallet-category').val('');
        $('#lpa-wallet-modal-title').text('Add Wallet');
        $('#lpa-wallet-modal').show();
        if (id) {
            lpaPost('get_wallets', {}, function(res) {
                if (res.success) {
                    var w = res.data.find(function(r) { return r.id == id; });
                    if (w) {
                        $('#lpa-wallet-id').val(w.id);
                        $('#lpa-wallet-name').val(w.name);
                        $('#lpa-wallet-category').val(w.category);
                        $('#lpa-wallet-modal-title').text('Edit Wallet');
                    }
                }
            });
        }
    };

    window.lpaSubmitWallet = function(e) {
        e.preventDefault();
        var btn = $(e.target).find('button[type=submit');
        btn.prop('disabled', true).text('Saving...');
        lpaPost('save_wallet', {
            id: $('#lpa-wallet-id').val(),
            name: $('#lpa-wallet-name').val(),
            category: $('#lpa-wallet-category').val()
        }, function(res) {
            btn.prop('disabled', false).text('Save');
            if (res.success) {
                lpaToast(res.data.message);
                lpaCloseModal();
                lpaLoadWallets();
            } else {
                lpaToast(res.data.message || 'Error', 'error');
            }
        });
        return false;
    };

    window.lpaDeleteWallet = function(id) {
        if (!confirm('Delete this wallet?')) return;
        lpaPost('delete_wallet', { id: id }, function(res) {
            if (res.success) {
                lpaToast(res.data.message);
                lpaLoadWallets();
            }
        });
    };

    function lpaLoadWallets() {
        lpaPost('get_wallets', {}, function(res) {
            if (!res.success || !res.data.length) {
                $('#lpa-wallets-table').html('<div class="lpa-empty">No wallets found. Create one to get started.</div>');
                return;
            }
            var html = '<table class="lpa-table"><thead><tr><th>ID</th><th>Name</th><th>Category</th><th>Created</th><th>Actions</th></tr></thead><tbody>';
            res.data.forEach(function(w) {
                html += '<tr><td>' + w.id + '</td><td>' + w.name + '</td><td><span class="lpa-badge lpa-badge-' + w.category + '">' + w.category + '</span></td><td>' + lpaFormatDate(w.created_at) + '</td><td class="lpa-actions"><button class="lpa-btn-sm" onclick="lpaWalletModal(' + w.id + ')">Edit</button> <button class="lpa-btn-sm lpa-btn-danger" onclick="lpaDeleteWallet(' + w.id + ')">Delete</button></td></tr>';
            });
            html += '</tbody></table>';
            $('#lpa-wallets-table').html(html);
        });
    }

    // --- INCOMES ---
    window.lpaIncomeModal = function(id) {
        $('#lpa-income-id').val('');
        $('#lpa-income-wallet').val('');
        $('#lpa-income-amount').val('');
        $('#lpa-income-description').val('');
        $('#lpa-income-currency').val('USD');
        $('#lpa-income-modal-title').text('Add Income');
        lpaLoadIncomeWallets();
        $('#lpa-income-modal').show();
        if (id) {
            lpaPost('get_incomes', {}, function(res) {
                if (res.success) {
                    var w = res.data.find(function(r) { return r.id == id; });
                    if (w) {
                        $('#lpa-income-id').val(w.id);
                        $('#lpa-income-wallet').val(w.fk_wallet_id);
                        $('#lpa-income-amount').val(w.amount);
                        $('#lpa-income-description').val(w.description);
                        $('#lpa-income-currency').val(w.currency);
                        $('#lpa-income-modal-title').text('Edit Income');
                    }
                }
            });
        }
    };

    function lpaLoadIncomeWallets() {
        lpaPost('get_wallets', {}, function(res) {
            if (res.success) {
                var sel = $('#lpa-income-wallet');
                sel.html('<option value="">Select wallet</option>');
                res.data.forEach(function(w) {
                    if (w.category === 'income') {
                        sel.append('<option value="' + w.id + '">' + w.name + '</option>');
                    }
                });
            }
        });
    }

    window.lpaSubmitIncome = function(e) {
        e.preventDefault();
        var btn = $(e.target).find('button[type=submit]');
        btn.prop('disabled', true).text('Saving...');
        lpaPost('save_income', {
            id: $('#lpa-income-id').val(),
            wallet_id: $('#lpa-income-wallet').val(),
            amount: $('#lpa-income-amount').val(),
            description: $('#lpa-income-description').val(),
            currency: $('#lpa-income-currency').val()
        }, function(res) {
            btn.prop('disabled', false).text('Save');
            if (res.success) {
                lpaToast(res.data.message);
                lpaCloseModal();
                lpaLoadIncomes();
            } else {
                lpaToast(res.data.message || 'Error', 'error');
            }
        });
        return false;
    };

    window.lpaDeleteIncome = function(id) {
        if (!confirm('Delete this income?')) return;
        lpaPost('delete_income', { id: id }, function(res) {
            if (res.success) {
                lpaToast(res.data.message);
                lpaLoadIncomes();
            }
        });
    };

    function lpaLoadIncomes() {
        lpaPost('get_incomes', {}, function(res) {
            if (!res.success || !res.data.length) {
                $('#lpa-incomes-table').html('<div class="lpa-empty">No incomes found. Add one to get started.</div>');
                return;
            }
            var html = '<table class="lpa-table"><thead><tr><th>ID</th><th>Wallet</th><th>Amount</th><th>Currency</th><th>Description</th><th>Date</th><th>Actions</th></tr></thead><tbody>';
            res.data.forEach(function(r) {
                html += '<tr><td>' + r.id + '</td><td>' + (r.wallet_name || '-') + '</td><td class="lpa-amount-income">' + lpaFormatMoney(r.amount) + '</td><td>' + (r.currency || '') + '</td><td>' + (r.description || '') + '</td><td>' + lpaFormatDate(r.created_at) + '</td><td class="lpa-actions"><button class="lpa-btn-sm" onclick="lpaIncomeModal(' + r.id + ')">Edit</button> <button class="lpa-btn-sm lpa-btn-danger" onclick="lpaDeleteIncome(' + r.id + ')">Delete</button></td></tr>';
            });
            html += '</tbody></table>';
            $('#lpa-incomes-table').html(html);
        });
    }

    // --- EXPENSES ---
    window.lpaExpenseModal = function(id) {
        $('#lpa-expense-id').val('');
        $('#lpa-expense-wallet').val('');
        $('#lpa-expense-amount').val('');
        $('#lpa-expense-description').val('');
        $('#lpa-expense-currency').val('USD');
        $('#lpa-expense-modal-title').text('Add Expense');
        lpaLoadExpenseWallets();
        $('#lpa-expense-modal').show();
        if (id) {
            lpaPost('get_expenses', {}, function(res) {
                if (res.success) {
                    var w = res.data.find(function(r) { return r.id == id; });
                    if (w) {
                        $('#lpa-expense-id').val(w.id);
                        $('#lpa-expense-wallet').val(w.fk_wallet_id);
                        $('#lpa-expense-amount').val(w.amount);
                        $('#lpa-expense-description').val(w.description);
                        $('#lpa-expense-currency').val(w.currency);
                        $('#lpa-expense-modal-title').text('Edit Expense');
                    }
                }
            });
        }
    };

    function lpaLoadExpenseWallets() {
        lpaPost('get_wallets', {}, function(res) {
            if (res.success) {
                var sel = $('#lpa-expense-wallet');
                sel.html('<option value="">Select wallet</option>');
                res.data.forEach(function(w) {
                    if (w.category === 'expense') {
                        sel.append('<option value="' + w.id + '">' + w.name + '</option>');
                    }
                });
            }
        });
    }

    window.lpaSubmitExpense = function(e) {
        e.preventDefault();
        var btn = $(e.target).find('button[type=submit]');
        btn.prop('disabled', true).text('Saving...');
        lpaPost('save_expense', {
            id: $('#lpa-expense-id').val(),
            wallet_id: $('#lpa-expense-wallet').val(),
            amount: $('#lpa-expense-amount').val(),
            description: $('#lpa-expense-description').val(),
            currency: $('#lpa-expense-currency').val()
        }, function(res) {
            btn.prop('disabled', false).text('Save');
            if (res.success) {
                lpaToast(res.data.message);
                lpaCloseModal();
                lpaLoadExpenses();
            } else {
                lpaToast(res.data.message || 'Error', 'error');
            }
        });
        return false;
    };

    window.lpaDeleteExpense = function(id) {
        if (!confirm('Delete this expense?')) return;
        lpaPost('delete_expense', { id: id }, function(res) {
            if (res.success) {
                lpaToast(res.data.message);
                lpaLoadExpenses();
            }
        });
    };

    function lpaLoadExpenses() {
        lpaPost('get_expenses', {}, function(res) {
            if (!res.success || !res.data.length) {
                $('#lpa-expenses-table').html('<div class="lpa-empty">No expenses found. Add one to get started.</div>');
                return;
            }
            var html = '<table class="lpa-table"><thead><tr><th>ID</th><th>Wallet</th><th>Amount</th><th>Currency</th><th>Description</th><th>Date</th><th>Actions</th></tr></thead><tbody>';
            res.data.forEach(function(r) {
                html += '<tr><td>' + r.id + '</td><td>' + (r.wallet_name || '-') + '</td><td class="lpa-amount-expense">' + lpaFormatMoney(r.amount) + '</td><td>' + (r.currency || '') + '</td><td>' + (r.description || '') + '</td><td>' + lpaFormatDate(r.created_at) + '</td><td class="lpa-actions"><button class="lpa-btn-sm" onclick="lpaExpenseModal(' + r.id + ')">Edit</button> <button class="lpa-btn-sm lpa-btn-danger" onclick="lpaDeleteExpense(' + r.id + ')">Delete</button></td></tr>';
            });
            html += '</tbody></table>';
            $('#lpa-expenses-table').html(html);
        });
    }

    // --- CASHBOOK ---
    function lpaLoadCashbook() {
        lpaPost('get_cashbook', {}, function(res) {
            if (!res.success || !res.data.length) {
                $('#lpa-cashbook-table').html('<div class="lpa-empty">No cashbook entries found.</div>');
                return;
            }
            var html = '<table class="lpa-table"><thead><tr><th>ID</th><th>Type</th><th>In Amount</th><th>Out Amount</th><th>Ref ID</th><th>Date</th></tr></thead><tbody>';
            var totalIn = 0, totalOut = 0;
            res.data.forEach(function(r) {
                var type = r.reference_type || '-';
                totalIn += parseFloat(r.in_amount || 0);
                totalOut += parseFloat(r.out_amount || 0);
                html += '<tr><td>' + r.id + '</td><td><span class="lpa-badge lpa-badge-' + type + '">' + type + '</span></td><td class="lpa-amount-income">' + (r.in_amount ? lpaFormatMoney(r.in_amount) : '-') + '</td><td class="lpa-amount-expense">' + (r.out_amount ? lpaFormatMoney(r.out_amount) : '-') + '</td><td>' + r.fk_reference_id + '</td><td>' + lpaFormatDate(r.created_at) + '</td></tr>';
            });
            html += '</tbody></table>';
            html += '<div class="lpa-cashbook-summary"><strong>Total In:</strong> <span class="lpa-amount-income">' + lpaFormatMoney(totalIn) + '</span> | <strong>Total Out:</strong> <span class="lpa-amount-expense">' + lpaFormatMoney(totalOut) + '</span></div>';
            $('#lpa-cashbook-table').html(html);
        });
    }

    // --- ACTIVITIES ---
    function lpaLoadActivities() {
        lpaPost('get_activities', {}, function(res) {
            if (!res.success || !res.data.length) {
                $('#lpa-activities-table').html('<div class="lpa-empty">No activities found.</div>');
                return;
            }
            var html = '<table class="lpa-table"><thead><tr><th>ID</th><th>Type</th><th>Name</th><th>IP</th><th>Date</th></tr></thead><tbody>';
            res.data.forEach(function(r) {
                html += '<tr><td>' + r.id + '</td><td><span class="lpa-badge lpa-badge-' + r.type + '">' + r.type + '</span></td><td>' + r.name + '</td><td>' + (r.ip_address || '-') + '</td><td>' + lpaFormatDate(r.created_at) + '</td></tr>';
            });
            html += '</tbody></table>';
            $('#lpa-activities-table').html(html);
        });
    }

    // --- SETTINGS ---
    function lpaLoadSettings() {
        lpaPost('get_settings', {}, function(res) {
            if (!res.success || !res.data.length) {
                $('#lpa-settings-list').html('<div class="lpa-empty">No settings found.</div>');
                return;
            }
            var html = '<table class="lpa-table"><thead><tr><th>Name</th><th>Setting</th></tr></thead><tbody>';
            res.data.forEach(function(r) {
                var val = r.setting;
                try { val = JSON.parse(val); if (typeof val === 'object') val = JSON.stringify(val); } catch(e) {}
                html += '<tr><td><strong>' + r.name + '</strong></td><td>' + val + '</td></tr>';
            });
            html += '</tbody></table>';
            $('#lpa-settings-list').html(html);
        });
    }

    // --- MODAL ---
    window.lpaCloseModal = function() {
        $('.lpa-modal').hide();
    };

    // --- TOAST ---
    window.lpaToast = lpaToast;

    // --- INIT ---
    if ($('#lpa-wallets-table').length) lpaLoadWallets();
    if ($('#lpa-incomes-table').length) lpaLoadIncomes();
    if ($('#lpa-expenses-table').length) lpaLoadExpenses();
    if ($('#lpa-cashbook-table').length) lpaLoadCashbook();
    if ($('#lpa-activities-table').length) lpaLoadActivities();
    if ($('#lpa-settings-list').length) lpaLoadSettings();
});
