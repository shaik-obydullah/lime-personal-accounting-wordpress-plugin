jQuery(document).ready(function($) {

    // --- Helper ---
    function opaPost(action, data, callback) {
        $.post(opaAjax.ajaxurl, {
            action: 'opa_action',
            nonce: opaAjax.nonce,
            opa_action: action,
            data: data || {}
        }, function(res) {
            if (callback) callback(res);
        }).fail(function() {
            opaToast('Request failed', 'error');
        });
    }

    function opaToast(msg, type) {
        var toast = $('<div class="opa-toast opa-toast-' + (type || 'success') + '">' + msg + '</div>');
        $('body').append(toast);
        setTimeout(function() { toast.addClass('opa-toast-show'); }, 10);
        setTimeout(function() { toast.removeClass('opa-toast-show'); setTimeout(function() { toast.remove(); }, 300); }, 3000);
    }

    function opaLoadingHtml() {
        return '<div class="opa-loading"><span class="opa-spinner"></span>Loading...</div>';
    }

    function opaFormatDate(d) {
        if (!d) return '-';
        var dt = new Date(d);
        return dt.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' });
    }

    function opaFormatMoney(amt) {
        if (!amt) return '0.00';
        var num = parseFloat(amt).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
        return (opaSettings.currency_symbol || '') + num;
    }

    // --- SETTINGS STORE ---
    var opaSettings = {
        default_currency: 'USD',
        currency_symbol: '',
        page_size: 10,
        default_wallet_id: 0
    };

    var opaCurrencySymbols = {
        'USD': '$',
        'EUR': '€',
        'GBP': '£',
        'BDT': '৳',
        'INR': '₹',
        'PKR': '₨',
        'CAD': 'C$',
        'AUD': 'A$',
        'JPY': '¥',
        'CNY': 'CN¥',
        'AED': 'د.إ',
        'SAR': '﷼',
        'MYR': 'RM',
        'SGD': 'S$',
        'NZD': 'NZ$'
    };

    function opaSymbolFor(currency) {
        return opaCurrencySymbols[String(currency || '').toUpperCase()] || '';
    }

    // --- PAGINATION ---
    function opaPaginate(rows, page) {
        var size = parseInt(opaSettings.page_size, 10) || 10;
        var total = rows.length;
        var totalPages = Math.max(1, Math.ceil(total / size));
        if (page < 1) page = 1;
        if (page > totalPages) page = totalPages;
        return {
            page: page,
            totalPages: totalPages,
            start: (page - 1) * size,
            end: Math.min(page * size, total)
        };
    }

    function opaPagerHtml(container, page, totalPages) {
        if (totalPages <= 1) return '';
        var html = '<div class="opa-pager"><span class="opa-pager-info">' + page + ' / ' + totalPages + '</span>';
        html += '<button type="button" class="opa-btn-sm" data-opa-pager="' + container + '" data-page="' + (page - 1) + '"' + (page <= 1 ? ' disabled' : '') + '>Prev</button>';
        html += '<button type="button" class="opa-btn-sm" data-opa-pager="' + container + '" data-page="' + (page + 1) + '"' + (page >= totalPages ? ' disabled' : '') + '>Next</button>';
        html += '</div>';
        return html;
    }

    $(document).on('click', '.opa-pager button[data-page]:not([disabled])', function() {
        var target = $(this).data('opa-pager');
        $(document.getElementById(target)).data('page', parseInt($(this).data('page'), 10) || 1);
        var loader = {
            'opa-wallets-table': opaLoadWallets,
            'opa-incomes-table': opaLoadIncomes,
            'opa-expenses-table': opaLoadExpenses,
            'opa-cashbook-table': opaLoadCashbook,
            'opa-activities-table': opaLoadActivities
        }[target];
        if (typeof loader === 'function') {
            loader();
        }
    });

    // --- WALLETS ---
    window.opaWalletModal = function(id) {
        $('#opa-wallet-id').val('');
        $('#opa-wallet-name').val('');
        $('#opa-wallet-category').val('');
        $('#opa-wallet-modal-title').text('Add Wallet');
        $('#opa-wallet-modal').show();
        if (id) {
            opaPost('get_wallets', {}, function(res) {
                if (res.success) {
                    var w = res.data.find(function(r) { return r.id == id; });
                    if (w) {
                        $('#opa-wallet-id').val(w.id);
                        $('#opa-wallet-name').val(w.name);
                        $('#opa-wallet-category').val(w.category);
                        $('#opa-wallet-modal-title').text('Edit Wallet');
                    }
                }
            });
        }
    };

    window.opaSubmitWallet = function(e) {
        e.preventDefault();
        var btn = $(e.target).find('button[type=submit]');
        btn.prop('disabled', true).text('Saving...');
        opaPost('save_wallet', {
            id: $('#opa-wallet-id').val(),
            name: $('#opa-wallet-name').val(),
            category: $('#opa-wallet-category').val()
        }, function(res) {
            btn.prop('disabled', false).text('Save');
            if (res.success) {
                opaToast(res.data.message);
                opaCloseModal();
                opaLoadWallets();
            } else {
                opaToast(res.data.message || 'Error', 'error');
            }
        });
        return false;
    };

    window.opaDeleteWallet = function(id) {
        if (!confirm('Delete this wallet?')) return;
        opaPost('delete_wallet', { id: id }, function(res) {
            if (res.success) {
                opaToast(res.data.message);
                opaLoadWallets();
            }
        });
    };

    function opaLoadWallets() {
        var table = $('#opa-wallets-table');
        table.attr('data-opa-list', 'wallets');
        table.html(opaLoadingHtml());
        opaPost('get_wallets', {}, function(res) {
            if (!res.success || !res.data.length) {
                table.html('<div class="opa-empty">No wallets found. Create one to get started.</div>');
                return;
            }
            var rows = res.data;
            var p = opaPaginate(rows, parseInt(table.data('page') || 1, 10));
            var html = '<table class="opa-table"><thead><tr><th>ID</th><th>Name</th><th>Category</th><th>Created</th><th>Actions</th></tr></thead><tbody>';
            rows.slice(p.start, p.end).forEach(function(w) {
                html += '<tr><td>' + w.id + '</td><td>' + w.name + '</td><td><span class="opa-badge opa-badge-' + w.category + '">' + w.category + '</span></td><td>' + opaFormatDate(w.created_at) + '</td><td class="opa-actions"><button class="opa-btn-sm" onclick="opaWalletModal(' + w.id + ')">Edit</button> <button class="opa-btn-sm opa-btn-danger" onclick="opaDeleteWallet(' + w.id + ')">Delete</button></td></tr>';
            });
            html += '</tbody></table>';
            html += opaPagerHtml('opa-wallets-table', p.page, p.totalPages);
            table.html(html);
        });
    }

    // --- INCOMES ---
    window.opaIncomeModal = function(id) {
        $('#opa-income-id').val('');
        $('#opa-income-wallet').val('');
        $('#opa-income-amount').val('');
        $('#opa-income-description').val('');
        $('#opa-income-currency').val(opaSettings.default_currency || 'USD');
        $('#opa-income-modal-title').text('Add Income');
        opaLoadIncomeWallets();
        $('#opa-income-modal').show();
        if (id) {
            opaPost('get_incomes', {}, function(res) {
                if (res.success) {
                    var w = res.data.find(function(r) { return r.id == id; });
                    if (w) {
                        $('#opa-income-id').val(w.id);
                        $('#opa-income-wallet').val(w.fk_wallet_id);
                        $('#opa-income-amount').val(w.amount);
                        $('#opa-income-description').val(w.description);
                        $('#opa-income-currency').val(w.currency);
                        $('#opa-income-modal-title').text('Edit Income');
                    }
                }
            });
        }
    };

    function opaLoadIncomeWallets() {
        opaPost('get_wallets', {}, function(res) {
            if (res.success) {
                var sel = $('#opa-income-wallet');
                sel.html('<option value="">Select wallet</option>');
                res.data.forEach(function(w) {
                    if (w.category === 'income') {
                        sel.append('<option value="' + w.id + '">' + w.name + '</option>');
                    }
                });
                if (!$('#opa-income-id').val() && opaSettings.default_wallet_id) {
                    sel.val(String(opaSettings.default_wallet_id));
                }
            }
        });
    }

    window.opaSubmitIncome = function(e) {
        e.preventDefault();
        var btn = $(e.target).find('button[type=submit]');
        btn.prop('disabled', true).text('Saving...');
        opaPost('save_income', {
            id: $('#opa-income-id').val(),
            wallet_id: $('#opa-income-wallet').val(),
            amount: $('#opa-income-amount').val(),
            description: $('#opa-income-description').val(),
            currency: $('#opa-income-currency').val()
        }, function(res) {
            btn.prop('disabled', false).text('Save');
            if (res.success) {
                opaToast(res.data.message);
                opaCloseModal();
                opaLoadIncomes();
            } else {
                opaToast(res.data.message || 'Error', 'error');
            }
        });
        return false;
    };

    window.opaDeleteIncome = function(id) {
        if (!confirm('Delete this income?')) return;
        opaPost('delete_income', { id: id }, function(res) {
            if (res.success) {
                opaToast(res.data.message);
                opaLoadIncomes();
            }
        });
    };

    function opaLoadIncomes() {
        var table = $('#opa-incomes-table');
        table.attr('data-opa-list', 'incomes');
        table.html(opaLoadingHtml());
        opaPost('get_incomes', {}, function(res) {
            if (!res.success || !res.data.length) {
                table.html('<div class="opa-empty">No incomes found. Add one to get started.</div>');
                return;
            }
            var rows = res.data;
            var p = opaPaginate(rows, parseInt(table.data('page') || 1, 10));
            var html = '<table class="opa-table"><thead><tr><th>ID</th><th>Wallet</th><th>Amount</th><th>Currency</th><th>Description</th><th>Date</th><th>Actions</th></tr></thead><tbody>';
            rows.slice(p.start, p.end).forEach(function(r) {
                html += '<tr><td>' + r.id + '</td><td>' + (r.wallet_name || '-') + '</td><td class="opa-amount-income">' + opaFormatMoney(r.amount) + '</td><td>' + (r.currency || '') + '</td><td>' + (r.description || '') + '</td><td>' + opaFormatDate(r.created_at) + '</td><td class="opa-actions"><button class="opa-btn-sm" onclick="opaIncomeModal(' + r.id + ')">Edit</button> <button class="opa-btn-sm opa-btn-danger" onclick="opaDeleteIncome(' + r.id + ')">Delete</button></td></tr>';
            });
            html += '</tbody></table>';
            html += opaPagerHtml('opa-incomes-table', p.page, p.totalPages);
            table.html(html);
        });
    }

    // --- EXPENSES ---
    window.opaExpenseModal = function(id) {
        $('#opa-expense-id').val('');
        $('#opa-expense-wallet').val('');
        $('#opa-expense-amount').val('');
        $('#opa-expense-description').val('');
        $('#opa-expense-currency').val(opaSettings.default_currency || 'USD');
        $('#opa-expense-modal-title').text('Add Expense');
        opaLoadExpenseWallets();
        $('#opa-expense-modal').show();
        if (id) {
            opaPost('get_expenses', {}, function(res) {
                if (res.success) {
                    var w = res.data.find(function(r) { return r.id == id; });
                    if (w) {
                        $('#opa-expense-id').val(w.id);
                        $('#opa-expense-wallet').val(w.fk_wallet_id);
                        $('#opa-expense-amount').val(w.amount);
                        $('#opa-expense-description').val(w.description);
                        $('#opa-expense-currency').val(w.currency);
                        $('#opa-expense-modal-title').text('Edit Expense');
                    }
                }
            });
        }
    };

    function opaLoadExpenseWallets() {
        opaPost('get_wallets', {}, function(res) {
            if (res.success) {
                var sel = $('#opa-expense-wallet');
                sel.html('<option value="">Select wallet</option>');
                res.data.forEach(function(w) {
                    if (w.category === 'expense') {
                        sel.append('<option value="' + w.id + '">' + w.name + '</option>');
                    }
                });
                if (!$('#opa-expense-id').val() && opaSettings.default_wallet_id) {
                    sel.val(String(opaSettings.default_wallet_id));
                }
            }
        });
    }

    window.opaSubmitExpense = function(e) {
        e.preventDefault();
        var btn = $(e.target).find('button[type=submit]');
        btn.prop('disabled', true).text('Saving...');
        opaPost('save_expense', {
            id: $('#opa-expense-id').val(),
            wallet_id: $('#opa-expense-wallet').val(),
            amount: $('#opa-expense-amount').val(),
            description: $('#opa-expense-description').val(),
            currency: $('#opa-expense-currency').val()
        }, function(res) {
            btn.prop('disabled', false).text('Save');
            if (res.success) {
                opaToast(res.data.message);
                opaCloseModal();
                opaLoadExpenses();
            } else {
                opaToast(res.data.message || 'Error', 'error');
            }
        });
        return false;
    };

    window.opaDeleteExpense = function(id) {
        if (!confirm('Delete this expense?')) return;
        opaPost('delete_expense', { id: id }, function(res) {
            if (res.success) {
                opaToast(res.data.message);
                opaLoadExpenses();
            }
        });
    };

    function opaLoadExpenses() {
        var table = $('#opa-expenses-table');
        table.attr('data-opa-list', 'expenses');
        table.html(opaLoadingHtml());
        opaPost('get_expenses', {}, function(res) {
            if (!res.success || !res.data.length) {
                table.html('<div class="opa-empty">No expenses found. Add one to get started.</div>');
                return;
            }
            var rows = res.data;
            var p = opaPaginate(rows, parseInt(table.data('page') || 1, 10));
            var html = '<table class="opa-table"><thead><tr><th>ID</th><th>Wallet</th><th>Amount</th><th>Currency</th><th>Description</th><th>Date</th><th>Actions</th></tr></thead><tbody>';
            rows.slice(p.start, p.end).forEach(function(r) {
                html += '<tr><td>' + r.id + '</td><td>' + (r.wallet_name || '-') + '</td><td class="opa-amount-expense">' + opaFormatMoney(r.amount) + '</td><td>' + (r.currency || '') + '</td><td>' + (r.description || '') + '</td><td>' + opaFormatDate(r.created_at) + '</td><td class="opa-actions"><button class="opa-btn-sm" onclick="opaExpenseModal(' + r.id + ')">Edit</button> <button class="opa-btn-sm opa-btn-danger" onclick="opaDeleteExpense(' + r.id + ')">Delete</button></td></tr>';
            });
            html += '</tbody></table>';
            html += opaPagerHtml('opa-expenses-table', p.page, p.totalPages);
            table.html(html);
        });
    }

    // --- CASHBOOK ---
    function opaLoadCashbook() {
        var table = $('#opa-cashbook-table');
        table.attr('data-opa-list', 'cashbook');
        table.html(opaLoadingHtml());
        opaPost('get_cashbook', {}, function(res) {
            if (!res.success || !res.data.length) {
                table.html('<div class="opa-empty">No cashbook entries found.</div>');
                return;
            }
            var rows = res.data;
            var p = opaPaginate(rows, parseInt(table.data('page') || 1, 10));
            var html = '<table class="opa-table"><thead><tr><th>ID</th><th>Type</th><th>In Amount</th><th>Out Amount</th><th>Ref ID</th><th>Date</th></tr></thead><tbody>';
            var totalIn = 0, totalOut = 0;
            rows.forEach(function(r) {
                totalIn += parseFloat(r.in_amount || 0);
                totalOut += parseFloat(r.out_amount || 0);
            });
            rows.slice(p.start, p.end).forEach(function(r) {
                var type = r.reference_type || '-';
                html += '<tr><td>' + r.id + '</td><td><span class="opa-badge opa-badge-' + type + '">' + type + '</span></td><td class="opa-amount-income">' + (r.in_amount ? opaFormatMoney(r.in_amount) : '-') + '</td><td class="opa-amount-expense">' + (r.out_amount ? opaFormatMoney(r.out_amount) : '-') + '</td><td>' + r.fk_reference_id + '</td><td>' + opaFormatDate(r.created_at) + '</td></tr>';
            });
            html += '</tbody></table>';
            html += opaPagerHtml('opa-cashbook-table', p.page, p.totalPages);
            html += '<div class="opa-cashbook-summary"><strong>Total In:</strong> <span class="opa-amount-income">' + opaFormatMoney(totalIn) + '</span> | <strong>Total Out:</strong> <span class="opa-amount-expense">' + opaFormatMoney(totalOut) + '</span></div>';
            table.html(html);
        });
    }

    // --- ACTIVITIES ---
    function opaLoadActivities() {
        var table = $('#opa-activities-table');
        table.attr('data-opa-list', 'activities');
        table.html(opaLoadingHtml());
        opaPost('get_activities', {}, function(res) {
            if (!res.success || !res.data.length) {
                table.html('<div class="opa-empty">No activities found.</div>');
                return;
            }
            var rows = res.data;
            var p = opaPaginate(rows, parseInt(table.data('page') || 1, 10));
            var html = '<table class="opa-table"><thead><tr><th>ID</th><th>Type</th><th>Name</th><th>IP</th><th>Date</th></tr></thead><tbody>';
            rows.slice(p.start, p.end).forEach(function(r) {
                html += '<tr><td>' + r.id + '</td><td><span class="opa-badge opa-badge-' + r.type + '">' + r.type + '</span></td><td>' + r.name + '</td><td>' + (r.ip_address || '-') + '</td><td>' + opaFormatDate(r.created_at) + '</td></tr>';
            });
            html += '</tbody></table>';
            html += opaPagerHtml('opa-activities-table', p.page, p.totalPages);
            table.html(html);
        });
    }

    // --- SETTINGS ---
    function opaFetchSettings(callback) {
        opaPost('get_settings', {}, function(res) {
            if (res.success) {
                var s = {};
                (res.data || []).forEach(function(r) {
                    var v = r.setting;
                    try { v = JSON.parse(v); } catch (e) {}
                    s[r.name] = v;
                });
                $.extend(opaSettings, s);
                if (!opaSettings.default_currency) opaSettings.default_currency = 'USD';
                if (!opaSettings.page_size) opaSettings.page_size = 10;
            }
            if (callback) callback();
        });
    }

    function opaLoadSettings() {
        $('#opa-settings-currency').val(opaSettings.default_currency);
        $('#opa-settings-symbol').val(opaSettings.currency_symbol || opaSymbolFor(opaSettings.default_currency));
        $('#opa-settings-page-size').val(String(opaSettings.page_size));
        opaLoadDefaultWallets();
    }

    $('#opa-settings-currency').on('change', function() {
        var symbol = opaSymbolFor($(this).val());
        if (symbol) {
            $('#opa-settings-symbol').val(symbol);
        }
    });

    function opaLoadDefaultWallets() {
        var sel = $('#opa-settings-default-wallet');
        var loading = $('#opa-settings-wallet-loading');
        sel.prop('disabled', true).html('<option value="">Loading wallets...</option>');
        loading.show();
        opaPost('get_wallets', {}, function(res) {
            loading.hide();
            sel.prop('disabled', false);
            if (res.success) {
                sel.html('<option value="">None</option>');
                res.data.forEach(function(w) {
                    sel.append('<option value="' + w.id + '">' + w.name + ' (' + w.category + ')</option>');
                });
            }
            if (opaSettings.default_wallet_id) {
                sel.val(String(opaSettings.default_wallet_id));
            }
        });
    }

    window.opaSaveSettings = function(e) {
        e.preventDefault();
        var btn = $(e.target).find('button[type=submit]');
        btn.prop('disabled', true).text('Saving...');
        opaPost('save_settings', {
            default_currency: $('#opa-settings-currency').val(),
            currency_symbol: $('#opa-settings-symbol').val(),
            page_size: $('#opa-settings-page-size').val(),
            default_wallet_id: $('#opa-settings-default-wallet').val() || 0
        }, function(res) {
            btn.prop('disabled', false).text('Save Settings');
            if (res.success) {
                opaSettings.default_currency = $('#opa-settings-currency').val();
                opaSettings.currency_symbol = $('#opa-settings-symbol').val();
                opaSettings.page_size = parseInt($('#opa-settings-page-size').val(), 10) || 10;
                opaSettings.default_wallet_id = parseInt($('#opa-settings-default-wallet').val() || 0, 10) || 0;
                opaToast(res.data.message);
            } else {
                opaToast(res.data.message || 'Error', 'error');
            }
        });
        return false;
    };

    window.opaResetData = function() {
        if (!window.confirm('This will permanently delete ALL wallets, transactions, cashbook entries, activities and saved settings. This cannot be undone. Continue?')) return;
        opaPost('reset_data', {}, function(res) {
            if (res.success) {
                opaToast(res.data.message);
                opaSettings.default_currency = 'USD';
                opaSettings.currency_symbol = '';
                opaSettings.page_size = 10;
                opaSettings.default_wallet_id = 0;
                opaLoadSettings();
            } else {
                opaToast(res.data.message || 'Error', 'error');
            }
        });
    };

    window.opaExportCsv = function() {
        window.location.href = opaAjax.ajaxurl + '?action=opa_action&nonce=' + encodeURIComponent(opaAjax.nonce) + '&opa_action=export_csv';
    };

    // --- MODAL ---
    window.opaCloseModal = function() {
        $('.opa-modal').hide();
    };

    // --- TOAST ---
    window.opaToast = opaToast;

    // --- INIT ---
    opaFetchSettings(function() {
        if ($('#opa-wallets-table').length) opaLoadWallets();
        if ($('#opa-incomes-table').length) opaLoadIncomes();
        if ($('#opa-expenses-table').length) opaLoadExpenses();
        if ($('#opa-cashbook-table').length) opaLoadCashbook();
        if ($('#opa-activities-table').length) opaLoadActivities();
        if ($('#opa-settings-form').length) opaLoadSettings();
    });
});
