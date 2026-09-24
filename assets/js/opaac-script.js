jQuery(document).ready(function($) {

    // --- Helper ---
    function opaacPost(action, data, callback) {
        $.post(opaacAjax.ajaxurl, {
            action: 'opaac_action',
            nonce: opaacAjax.nonce,
            opaac_action: action,
            data: data || {}
        }, function(res) {
            if (callback) callback(res);
        }).fail(function() {
            opaacToast('Request failed', 'error');
        });
    }

    function opaacToast(msg, type) {
        var toast = $('<div class="opaac-toast opaac-toast-' + (type || 'success') + '">' + msg + '</div>');
        $('body').append(toast);
        setTimeout(function() { toast.addClass('opaac-toast-show'); }, 10);
        setTimeout(function() { toast.removeClass('opaac-toast-show'); setTimeout(function() { toast.remove(); }, 300); }, 3000);
    }

    function opaacLoadingHtml() {
        return '<div class="opaac-loading"><span class="opaac-spinner"></span>Loading...</div>';
    }

    function opaacFormatDate(d) {
        if (!d) return '-';
        var dt = new Date(d);
        return dt.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' });
    }

    function opaacFormatMoney(amt) {
        if (!amt) return '0.00';
        var num = parseFloat(amt).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
        return (opaacSettings.currency_symbol || '') + num;
    }

    // --- SETTINGS STORE ---
    var opaacSettings = {
        default_currency: 'USD',
        currency_symbol: '',
        page_size: 10,
        default_wallet_id: 0
    };

    var opaacCurrencySymbols = {
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

    function opaacSymbolFor(currency) {
        return opaacCurrencySymbols[String(currency || '').toUpperCase()] || '';
    }

    // --- PAGINATION ---
    function opaacPaginate(rows, page) {
        var size = parseInt(opaacSettings.page_size, 10) || 10;
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

    function opaacPagerHtml(container, page, totalPages) {
        if (totalPages <= 1) return '';
        var html = '<div class="opaac-pager"><span class="opaac-pager-info">' + page + ' / ' + totalPages + '</span>';
        html += '<button type="button" class="opaac-btn-sm" data-opaac-pager="' + container + '" data-page="' + (page - 1) + '"' + (page <= 1 ? ' disabled' : '') + '>Prev</button>';
        html += '<button type="button" class="opaac-btn-sm" data-opaac-pager="' + container + '" data-page="' + (page + 1) + '"' + (page >= totalPages ? ' disabled' : '') + '>Next</button>';
        html += '</div>';
        return html;
    }

    $(document).on('click', '.opaac-pager button[data-page]:not([disabled])', function() {
        var target = $(this).data('opaac-pager');
        $(document.getElementById(target)).data('page', parseInt($(this).data('page'), 10) || 1);
        var loader = {
            'opaac-wallets-table': opaacLoadWallets,
            'opaac-incomes-table': opaacLoadIncomes,
            'opaac-expenses-table': opaacLoadExpenses,
            'opaac-cashbook-table': opaacLoadCashbook,
            'opaac-activities-table': opaacLoadActivities
        }[target];
        if (typeof loader === 'function') {
            loader();
        }
    });

    // --- WALLETS ---
    window.opaacWalletModal = function(id) {
        $('#opaac-wallet-id').val('');
        $('#opaac-wallet-name').val('');
        $('#opaac-wallet-category').val('');
        $('#opaac-wallet-modal-title').text('Add Wallet');
        $('#opaac-wallet-modal').show();
        if (id) {
            opaacPost('get_wallets', {}, function(res) {
                if (res.success) {
                    var w = res.data.find(function(r) { return r.id == id; });
                    if (w) {
                        $('#opaac-wallet-id').val(w.id);
                        $('#opaac-wallet-name').val(w.name);
                        $('#opaac-wallet-category').val(w.category);
                        $('#opaac-wallet-modal-title').text('Edit Wallet');
                    }
                }
            });
        }
    };

    window.opaacSubmitWallet = function(e) {
        e.preventDefault();
        var btn = $(e.target).find('button[type=submit]');
        btn.prop('disabled', true).text('Saving...');
        opaacPost('save_wallet', {
            id: $('#opaac-wallet-id').val(),
            name: $('#opaac-wallet-name').val(),
            category: $('#opaac-wallet-category').val()
        }, function(res) {
            btn.prop('disabled', false).text('Save');
            if (res.success) {
                opaacToast(res.data.message);
                opaacCloseModal();
                opaacLoadWallets();
            } else {
                opaacToast(res.data.message || 'Error', 'error');
            }
        });
        return false;
    };

    window.opaacDeleteWallet = function(id) {
        if (!confirm('Delete this wallet?')) return;
        opaacPost('delete_wallet', { id: id }, function(res) {
            if (res.success) {
                opaacToast(res.data.message);
                opaacLoadWallets();
            }
        });
    };

    function opaacLoadWallets() {
        var table = $('#opaac-wallets-table');
        table.attr('data-opaac-list', 'wallets');
        table.html(opaacLoadingHtml());
        opaacPost('get_wallets', {}, function(res) {
            if (!res.success || !res.data.length) {
                table.html('<div class="opaac-empty">No wallets found. Create one to get started.</div>');
                return;
            }
            var rows = res.data;
            var p = opaacPaginate(rows, parseInt(table.data('page') || 1, 10));
            var html = '<table class="opaac-table"><thead><tr><th>ID</th><th>Name</th><th>Category</th><th>Created</th><th>Actions</th></tr></thead><tbody>';
            rows.slice(p.start, p.end).forEach(function(w) {
                html += '<tr><td>' + w.id + '</td><td>' + w.name + '</td><td><span class="opaac-badge opaac-badge-' + w.category + '">' + w.category + '</span></td><td>' + opaacFormatDate(w.created_at) + '</td><td class="opaac-actions"><button class="opaac-btn-sm" onclick="opaacWalletModal(' + w.id + ')">Edit</button> <button class="opaac-btn-sm opaac-btn-danger" onclick="opaacDeleteWallet(' + w.id + ')">Delete</button></td></tr>';
            });
            html += '</tbody></table>';
            html += opaacPagerHtml('opaac-wallets-table', p.page, p.totalPages);
            table.html(html);
        });
    }

    // --- INCOMES ---
    window.opaacIncomeModal = function(id) {
        $('#opaac-income-id').val('');
        $('#opaac-income-wallet').val('');
        $('#opaac-income-amount').val('');
        $('#opaac-income-description').val('');
        $('#opaac-income-currency').val(opaacSettings.default_currency || 'USD');
        $('#opaac-income-modal-title').text('Add Income');
        opaacLoadIncomeWallets();
        $('#opaac-income-modal').show();
        if (id) {
            opaacPost('get_incomes', {}, function(res) {
                if (res.success) {
                    var w = res.data.find(function(r) { return r.id == id; });
                    if (w) {
                        $('#opaac-income-id').val(w.id);
                        $('#opaac-income-wallet').val(w.fk_wallet_id);
                        $('#opaac-income-amount').val(w.amount);
                        $('#opaac-income-description').val(w.description);
                        $('#opaac-income-currency').val(w.currency);
                        $('#opaac-income-modal-title').text('Edit Income');
                    }
                }
            });
        }
    };

    function opaacLoadIncomeWallets() {
        opaacPost('get_wallets', {}, function(res) {
            if (res.success) {
                var sel = $('#opaac-income-wallet');
                sel.html('<option value="">Select wallet</option>');
                res.data.forEach(function(w) {
                    if (w.category === 'income') {
                        sel.append('<option value="' + w.id + '">' + w.name + '</option>');
                    }
                });
                if (!$('#opaac-income-id').val() && opaacSettings.default_wallet_id) {
                    sel.val(String(opaacSettings.default_wallet_id));
                }
            }
        });
    }

    window.opaacSubmitIncome = function(e) {
        e.preventDefault();
        var btn = $(e.target).find('button[type=submit]');
        btn.prop('disabled', true).text('Saving...');
        opaacPost('save_income', {
            id: $('#opaac-income-id').val(),
            wallet_id: $('#opaac-income-wallet').val(),
            amount: $('#opaac-income-amount').val(),
            description: $('#opaac-income-description').val(),
            currency: $('#opaac-income-currency').val()
        }, function(res) {
            btn.prop('disabled', false).text('Save');
            if (res.success) {
                opaacToast(res.data.message);
                opaacCloseModal();
                opaacLoadIncomes();
            } else {
                opaacToast(res.data.message || 'Error', 'error');
            }
        });
        return false;
    };

    window.opaacDeleteIncome = function(id) {
        if (!confirm('Delete this income?')) return;
        opaacPost('delete_income', { id: id }, function(res) {
            if (res.success) {
                opaacToast(res.data.message);
                opaacLoadIncomes();
            }
        });
    };

    function opaacLoadIncomes() {
        var table = $('#opaac-incomes-table');
        table.attr('data-opaac-list', 'incomes');
        table.html(opaacLoadingHtml());
        opaacPost('get_incomes', {}, function(res) {
            if (!res.success || !res.data.length) {
                table.html('<div class="opaac-empty">No incomes found. Add one to get started.</div>');
                return;
            }
            var rows = res.data;
            var p = opaacPaginate(rows, parseInt(table.data('page') || 1, 10));
            var html = '<table class="opaac-table"><thead><tr><th>ID</th><th>Wallet</th><th>Amount</th><th>Currency</th><th>Description</th><th>Date</th><th>Actions</th></tr></thead><tbody>';
            rows.slice(p.start, p.end).forEach(function(r) {
                html += '<tr><td>' + r.id + '</td><td>' + (r.wallet_name || '-') + '</td><td class="opaac-amount-income">' + opaacFormatMoney(r.amount) + '</td><td>' + (r.currency || '') + '</td><td>' + (r.description || '') + '</td><td>' + opaacFormatDate(r.created_at) + '</td><td class="opaac-actions"><button class="opaac-btn-sm" onclick="opaacIncomeModal(' + r.id + ')">Edit</button> <button class="opaac-btn-sm opaac-btn-danger" onclick="opaacDeleteIncome(' + r.id + ')">Delete</button></td></tr>';
            });
            html += '</tbody></table>';
            html += opaacPagerHtml('opaac-incomes-table', p.page, p.totalPages);
            table.html(html);
        });
    }

    // --- EXPENSES ---
    window.opaacExpenseModal = function(id) {
        $('#opaac-expense-id').val('');
        $('#opaac-expense-wallet').val('');
        $('#opaac-expense-amount').val('');
        $('#opaac-expense-description').val('');
        $('#opaac-expense-currency').val(opaacSettings.default_currency || 'USD');
        $('#opaac-expense-modal-title').text('Add Expense');
        opaacLoadExpenseWallets();
        $('#opaac-expense-modal').show();
        if (id) {
            opaacPost('get_expenses', {}, function(res) {
                if (res.success) {
                    var w = res.data.find(function(r) { return r.id == id; });
                    if (w) {
                        $('#opaac-expense-id').val(w.id);
                        $('#opaac-expense-wallet').val(w.fk_wallet_id);
                        $('#opaac-expense-amount').val(w.amount);
                        $('#opaac-expense-description').val(w.description);
                        $('#opaac-expense-currency').val(w.currency);
                        $('#opaac-expense-modal-title').text('Edit Expense');
                    }
                }
            });
        }
    };

    function opaacLoadExpenseWallets() {
        opaacPost('get_wallets', {}, function(res) {
            if (res.success) {
                var sel = $('#opaac-expense-wallet');
                sel.html('<option value="">Select wallet</option>');
                res.data.forEach(function(w) {
                    if (w.category === 'expense') {
                        sel.append('<option value="' + w.id + '">' + w.name + '</option>');
                    }
                });
                if (!$('#opaac-expense-id').val() && opaacSettings.default_wallet_id) {
                    sel.val(String(opaacSettings.default_wallet_id));
                }
            }
        });
    }

    window.opaacSubmitExpense = function(e) {
        e.preventDefault();
        var btn = $(e.target).find('button[type=submit]');
        btn.prop('disabled', true).text('Saving...');
        opaacPost('save_expense', {
            id: $('#opaac-expense-id').val(),
            wallet_id: $('#opaac-expense-wallet').val(),
            amount: $('#opaac-expense-amount').val(),
            description: $('#opaac-expense-description').val(),
            currency: $('#opaac-expense-currency').val()
        }, function(res) {
            btn.prop('disabled', false).text('Save');
            if (res.success) {
                opaacToast(res.data.message);
                opaacCloseModal();
                opaacLoadExpenses();
            } else {
                opaacToast(res.data.message || 'Error', 'error');
            }
        });
        return false;
    };

    window.opaacDeleteExpense = function(id) {
        if (!confirm('Delete this expense?')) return;
        opaacPost('delete_expense', { id: id }, function(res) {
            if (res.success) {
                opaacToast(res.data.message);
                opaacLoadExpenses();
            }
        });
    };

    function opaacLoadExpenses() {
        var table = $('#opaac-expenses-table');
        table.attr('data-opaac-list', 'expenses');
        table.html(opaacLoadingHtml());
        opaacPost('get_expenses', {}, function(res) {
            if (!res.success || !res.data.length) {
                table.html('<div class="opaac-empty">No expenses found. Add one to get started.</div>');
                return;
            }
            var rows = res.data;
            var p = opaacPaginate(rows, parseInt(table.data('page') || 1, 10));
            var html = '<table class="opaac-table"><thead><tr><th>ID</th><th>Wallet</th><th>Amount</th><th>Currency</th><th>Description</th><th>Date</th><th>Actions</th></tr></thead><tbody>';
            rows.slice(p.start, p.end).forEach(function(r) {
                html += '<tr><td>' + r.id + '</td><td>' + (r.wallet_name || '-') + '</td><td class="opaac-amount-expense">' + opaacFormatMoney(r.amount) + '</td><td>' + (r.currency || '') + '</td><td>' + (r.description || '') + '</td><td>' + opaacFormatDate(r.created_at) + '</td><td class="opaac-actions"><button class="opaac-btn-sm" onclick="opaacExpenseModal(' + r.id + ')">Edit</button> <button class="opaac-btn-sm opaac-btn-danger" onclick="opaacDeleteExpense(' + r.id + ')">Delete</button></td></tr>';
            });
            html += '</tbody></table>';
            html += opaacPagerHtml('opaac-expenses-table', p.page, p.totalPages);
            table.html(html);
        });
    }

    // --- CASHBOOK ---
    function opaacLoadCashbook() {
        var table = $('#opaac-cashbook-table');
        table.attr('data-opaac-list', 'cashbook');
        table.html(opaacLoadingHtml());
        opaacPost('get_cashbook', {}, function(res) {
            if (!res.success || !res.data.length) {
                table.html('<div class="opaac-empty">No cashbook entries found.</div>');
                return;
            }
            var rows = res.data;
            var p = opaacPaginate(rows, parseInt(table.data('page') || 1, 10));
            var html = '<table class="opaac-table"><thead><tr><th>ID</th><th>Type</th><th>In Amount</th><th>Out Amount</th><th>Ref ID</th><th>Date</th></tr></thead><tbody>';
            var totalIn = 0, totalOut = 0;
            rows.forEach(function(r) {
                totalIn += parseFloat(r.in_amount || 0);
                totalOut += parseFloat(r.out_amount || 0);
            });
            rows.slice(p.start, p.end).forEach(function(r) {
                var type = r.reference_type || '-';
                html += '<tr><td>' + r.id + '</td><td><span class="opaac-badge opaac-badge-' + type + '">' + type + '</span></td><td class="opaac-amount-income">' + (r.in_amount ? opaacFormatMoney(r.in_amount) : '-') + '</td><td class="opaac-amount-expense">' + (r.out_amount ? opaacFormatMoney(r.out_amount) : '-') + '</td><td>' + r.fk_reference_id + '</td><td>' + opaacFormatDate(r.created_at) + '</td></tr>';
            });
            html += '</tbody></table>';
            html += opaacPagerHtml('opaac-cashbook-table', p.page, p.totalPages);
            html += '<div class="opaac-cashbook-summary"><strong>Total In:</strong> <span class="opaac-amount-income">' + opaacFormatMoney(totalIn) + '</span> | <strong>Total Out:</strong> <span class="opaac-amount-expense">' + opaacFormatMoney(totalOut) + '</span></div>';
            table.html(html);
        });
    }

    // --- ACTIVITIES ---
    function opaacLoadActivities() {
        var table = $('#opaac-activities-table');
        table.attr('data-opaac-list', 'activities');
        table.html(opaacLoadingHtml());
        opaacPost('get_activities', {}, function(res) {
            if (!res.success || !res.data.length) {
                table.html('<div class="opaac-empty">No activities found.</div>');
                return;
            }
            var rows = res.data;
            var p = opaacPaginate(rows, parseInt(table.data('page') || 1, 10));
            var html = '<table class="opaac-table"><thead><tr><th>ID</th><th>Type</th><th>Name</th><th>IP</th><th>Date</th></tr></thead><tbody>';
            rows.slice(p.start, p.end).forEach(function(r) {
                html += '<tr><td>' + r.id + '</td><td><span class="opaac-badge opaac-badge-' + r.type + '">' + r.type + '</span></td><td>' + r.name + '</td><td>' + (r.ip_address || '-') + '</td><td>' + opaacFormatDate(r.created_at) + '</td></tr>';
            });
            html += '</tbody></table>';
            html += opaacPagerHtml('opaac-activities-table', p.page, p.totalPages);
            table.html(html);
        });
    }

    // --- SETTINGS ---
    function opaacFetchSettings(callback) {
        opaacPost('get_settings', {}, function(res) {
            if (res.success) {
                var s = {};
                (res.data || []).forEach(function(r) {
                    var v = r.setting;
                    try { v = JSON.parse(v); } catch (e) {}
                    s[r.name] = v;
                });
                $.extend(opaacSettings, s);
                if (!opaacSettings.default_currency) opaacSettings.default_currency = 'USD';
                if (!opaacSettings.page_size) opaacSettings.page_size = 10;
            }
            if (callback) callback();
        });
    }

    function opaacLoadSettings() {
        $('#opaac-settings-currency').val(opaacSettings.default_currency);
        $('#opaac-settings-symbol').val(opaacSettings.currency_symbol || opaacSymbolFor(opaacSettings.default_currency));
        $('#opaac-settings-page-size').val(String(opaacSettings.page_size));
        opaacLoadDefaultWallets();
    }

    $('#opaac-settings-currency').on('change', function() {
        var symbol = opaacSymbolFor($(this).val());
        if (symbol) {
            $('#opaac-settings-symbol').val(symbol);
        }
    });

    function opaacLoadDefaultWallets() {
        var sel = $('#opaac-settings-default-wallet');
        var loading = $('#opaac-settings-wallet-loading');
        sel.prop('disabled', true).html('<option value="">Loading wallets...</option>');
        loading.show();
        opaacPost('get_wallets', {}, function(res) {
            loading.hide();
            sel.prop('disabled', false);
            if (res.success) {
                sel.html('<option value="">None</option>');
                res.data.forEach(function(w) {
                    sel.append('<option value="' + w.id + '">' + w.name + ' (' + w.category + ')</option>');
                });
            }
            if (opaacSettings.default_wallet_id) {
                sel.val(String(opaacSettings.default_wallet_id));
            }
        });
    }

    window.opaacSaveSettings = function(e) {
        e.preventDefault();
        var btn = $(e.target).find('button[type=submit]');
        btn.prop('disabled', true).text('Saving...');
        opaacPost('save_settings', {
            default_currency: $('#opaac-settings-currency').val(),
            currency_symbol: $('#opaac-settings-symbol').val(),
            page_size: $('#opaac-settings-page-size').val(),
            default_wallet_id: $('#opaac-settings-default-wallet').val() || 0
        }, function(res) {
            btn.prop('disabled', false).text('Save Settings');
            if (res.success) {
                opaacSettings.default_currency = $('#opaac-settings-currency').val();
                opaacSettings.currency_symbol = $('#opaac-settings-symbol').val();
                opaacSettings.page_size = parseInt($('#opaac-settings-page-size').val(), 10) || 10;
                opaacSettings.default_wallet_id = parseInt($('#opaac-settings-default-wallet').val() || 0, 10) || 0;
                opaacToast(res.data.message);
            } else {
                opaacToast(res.data.message || 'Error', 'error');
            }
        });
        return false;
    };

    window.opaacResetData = function() {
        if (!window.confirm('This will permanently delete ALL wallets, transactions, cashbook entries, activities and saved settings. This cannot be undone. Continue?')) return;
        opaacPost('reset_data', {}, function(res) {
            if (res.success) {
                opaacToast(res.data.message);
                opaacSettings.default_currency = 'USD';
                opaacSettings.currency_symbol = '';
                opaacSettings.page_size = 10;
                opaacSettings.default_wallet_id = 0;
                opaacLoadSettings();
            } else {
                opaacToast(res.data.message || 'Error', 'error');
            }
        });
    };

    window.opaacExportCsv = function() {
        window.location.href = opaacAjax.ajaxurl + '?action=opaac_action&nonce=' + encodeURIComponent(opaacAjax.nonce) + '&opaac_action=export_csv';
    };

    // --- MODAL ---
    window.opaacCloseModal = function() {
        $('.opaac-modal').hide();
    };

    // --- TOAST ---
    window.opaacToast = opaacToast;

    // --- INIT ---
    opaacFetchSettings(function() {
        if ($('#opaac-wallets-table').length) opaacLoadWallets();
        if ($('#opaac-incomes-table').length) opaacLoadIncomes();
        if ($('#opaac-expenses-table').length) opaacLoadExpenses();
        if ($('#opaac-cashbook-table').length) opaacLoadCashbook();
        if ($('#opaac-activities-table').length) opaacLoadActivities();
        if ($('#opaac-settings-form').length) opaacLoadSettings();
    });
});
