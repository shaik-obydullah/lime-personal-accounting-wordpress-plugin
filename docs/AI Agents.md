# AGENTS.md - Obydullah Personal Accounting Codebase Guide

## What This Project Is

A WordPress plugin for personal income/expense tracking. Runs inside WordPress admin as a self-contained accounting dashboard. Deployed via Docker Compose (WordPress + MySQL + phpMyAdmin).

## Project Structure

```
lime-opa-docker/
├── docker-compose.yml                    # 3-container stack (WP on :8090, PMA on :8091)
├── db_data/                              # MySQL persistent volume
├── wp-content/
│   ├── plugins/
│   │   └── obydullah-personal-accounting/     # THE PLUGIN (main codebase)
│   │       ├── obydullah-personal-accounting.php   # Entry point, hooks, asset loading
│   │       ├── includes/
│   │       │   ├── database.php               # Schema (6 tables), DB helpers
│   │       │   ├── admin-menu.php             # WP admin menu registration
│   │       │   ├── ajax-handlers.php          # All CRUD operations (12 actions)
│   │       │   ├── pages.php                  # Page controllers (data + view loading)
│   │       │   └── views/
│   │       │       ├── dashboard.php          # Stats cards (income/expense/balance)
│   │       │       ├── wallets.php            # Wallet CRUD + modal form
│   │       │       ├── incomes.php            # Income CRUD + modal form
│   │       │       ├── expenses.php           # Expense CRUD + modal form
│   │       │       ├── cashbook.php           # Transaction ledger (read-only)
│   │       │       ├── activities.php         # Activity log (read-only)
│   │       │       └── settings.php           # Config display (read-only)
│   │       └── assets/
│   │           ├── css/opa-style.css           # Full UI styling
│   │           └── js/opa-script.js            # jQuery AJAX frontend
│   ├── themes/
│   └── uploads/
└── case-study.html                       # TinyMCE-compatible case study content
```

## Quick Reference

### Entry Point
- `obydullah-personal-accounting.php:38-42` - `opa_init()` registers hooks
- `obydullah-personal-accounting.php:44-55` - `opa_enqueue_assets()` loads CSS/JS only on OPA pages

### Database (6 tables, all prefixed `opa_`)
| Table | Purpose | Key Relationships |
|-------|---------|-------------------|
| `wallets` | Income/expense categories | Parent for incomes/expenses |
| `incomes` | Income transactions | `fk_wallet_id` -> wallets |
| `expenses` | Expense transactions | `fk_wallet_id` -> wallets |
| `cashbook` | Auto-generated ledger | `fk_reference_id` + `reference_type` -> incomes/expenses |
| `activities` | Audit log | `fk_admin_id` -> wp_users |
| `configurations` | Settings store | Key-value with unique name |

All tables have: `id`, `created_at`, `created_by`, `updated_at`, `updated_by`, `deleted_at` (soft delete).

### AJAX Router (`ajax-handlers.php:4-53`)
Single endpoint: `wp_ajax_opa_action`. All requests go through `opa_ajax_router()`.

| Action | Handler | Creates Cashbook? |
|--------|---------|-------------------|
| `get_wallets` | `opa_get_wallets()` | No |
| `save_wallet` | `opa_save_wallet($data)` | No |
| `delete_wallet` | `opa_delete_wallet($data)` | No |
| `get_incomes` | `opa_get_incomes()` | No |
| `save_income` | `opa_save_income($data)` | **Yes** |
| `delete_income` | `opa_delete_income($data)` | Cascades soft delete |
| `get_expenses` | `opa_get_expenses()` | No |
| `save_expense` | `opa_save_expense($data)` | **Yes** |
| `delete_expense` | `opa_delete_expense($data)` | Cascades soft delete |
| `get_cashbook` | `opa_get_cashbook()` | No |
| `get_activities` | `opa_get_activities()` | No |
| `get_settings` | `opa_get_settings()` | No |
| `save_settings` | `opa_save_settings($data)` | No |
| `reset_data` | `opa_reset_data()` | Hard-deletes all tables |
| `export_csv` | `opa_export_csv()` | Streams CSV download (GET) |

### Helper Functions (database.php)
- `opa_db()` - Returns `$wpdb` instance
- `opa_table($name)` - Returns full table name: `{prefix}opa_{$name}`
- `opa_now()` - Returns `current_time('mysql')`
- `opa_user_id()` - Returns `get_current_user_id()`
- `opa_log_activity($type, $name)` - Logs to activities table (ajax-handlers.php:268)
- `opa_get_setting($name, $default)` / `opa_set_setting($name, $value)` - JSON key-value settings stored in `opa_configurations`

### Admin Pages (admin-menu.php)
Top-level menu: "Accounting" (dashicons-money-alt, position 30)
7 subpages: Dashboard, Wallets, Incomes, Expenses, Cashbook, Activities, Settings

### Frontend JS Pattern (opa-script.js)
- `opaPost(action, data, callback)` - Centralized AJAX helper
- Modal functions: `opaWalletModal(id)`, `opaIncomeModal(id)`, `opaExpenseModal(id)`
- Submit functions: `opaSubmitWallet(e)`, `opaSubmitIncome(e)`, `opaSubmitExpense(e)`
- Delete functions: `opaDeleteWallet(id)`, `opaDeleteIncome(id)`, `opaDeleteExpense(id)`
- Auto-init on `document.ready`: detects page by DOM element ID, loads data

### Wallet Categories
Wallets have a `category` field: `"income"` or `"expense"`.
- Income forms only show wallets with `category === 'income'`
- Expense forms only show wallets with `category === 'expense'`

## Core Business Logic

### Income/Expense Creation Flow
1. Insert into `opa_incomes` or `opa_expenses` with wallet_id, amount, description, currency
2. Auto-insert into `opa_cashbook` with `in_amount` or `out_amount` + reference to source
3. Log activity via `opa_log_activity()`
4. Return success with new ID

### Income/Expense Deletion Flow
1. Soft-delete from `opa_incomes`/`opa_expenses` (set `deleted_at`)
2. Soft-delete corresponding `opa_cashbook` entry (match `fk_reference_id` + `reference_type`)

### Dashboard Calculations
- `total_income` = `SUM(amount)` from incomes WHERE `deleted_at IS NULL`
- `total_expense` = `SUM(amount)` from expenses WHERE `deleted_at IS NULL`
- `balance` = `total_income - total_expense`
- Counts: income_count, expense_count, wallet_count

## Conventions

### PHP
- All functions prefixed with `opa_`
- Every file: `if (!defined('ABSPATH')) exit;`
- WordPress APIs for DB access (`$wpdb`), not raw PDO
- Input sanitization: `sanitize_text_field()`, `sanitize_textarea_field()`, `absint()`, `floatval()`
- Security: nonce (`opa_nonce`) + `manage_options` capability on every AJAX call

### JavaScript
- jQuery (bundled with WordPress), no build tools
- Functions exposed on `window` for inline `onclick` handlers
- Tables rendered via string concatenation (template literals avoided for WP compatibility)
- Toast notifications for all user feedback

### CSS
- All classes prefixed with `opa-`
- Color scheme: green (#22c55e) for income, red (#ef4444) for expense, blue (#3b82f6) for balance
- 12px border-radius, subtle shadows, responsive grid

## Development

### Run Locally
```bash
docker-compose up -d
# WordPress: http://localhost:8090
# phpMyAdmin: http://localhost:8091
```

Plugin is volume-mounted at `wp-content/plugins/obydullah-personal-accounting/` - changes reflect immediately.

### Database
- Host: `db` (from WordPress container) or `localhost:3306` (from host, if exposed)
- Name: `wordpress`
- User/Pass: `wordpress` / `wordpress`
- Root Pass: `root`
- Tables auto-created on plugin activation

### Testing Changes
1. Edit files in `wp-content/plugins/obydullah-personal-accounting/`
2. Refresh WordPress admin (no rebuild needed)
3. Plugin assets load only on pages with `opa_` prefix in screen ID

## Known Patterns

- No REST API - all communication via `wp_ajax` POST requests
- No front-end (admin-only plugin)
- No third-party PHP dependencies
- No build tools, transpilers, or package managers
- Cashbook is the single source of truth for all financial movement
