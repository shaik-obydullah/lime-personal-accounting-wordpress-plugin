# Lime Personal Accounting

A lightweight, self-hosted personal accounting plugin for WordPress. Track income, expenses, wallets, and generate an automatic cashbook ledger.

![PHP](https://img.shields.io/badge/PHP-8.0+-777bb4?logo=php&logoColor=white)
![WordPress](https://img.shields.io/badge/WordPress-6.0+-21759b?logo=wordpress&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-8.0-4479a1?logo=mysql&logoColor=white)
![Docker](https://img.shields.io/badge/Docker-Compose-2496ed?logo=docker&logoColor=white)
![Version](https://img.shields.io/badge/Version-1.0.0-22c55e)
![License](https://img.shields.io/badge/License-GPL--2.0-blue)

[Features](#features) · [Quick Start](#quick-start) · [Database](#database-schema) · [API](#api-actions) · [Development](#development) · [License](#license)

---

## Features

- **Dashboard** — Real-time income, expense, and balance overview with transaction counts
- **Wallet Management** — Create wallets categorized as income or expense
- **Income Tracking** — Log income with wallet, amount, description, and currency
- **Expense Tracking** — Log expenses with wallet, amount, description, and currency
- **Automatic Cashbook** — Every income/expense auto-generates a ledger entry
- **Activity Logging** — Audit trail for all financial actions with IP tracking
- **Soft Deletes** — No data is ever physically deleted; recoverable via database
- **Zero Dependencies** — No third-party PHP libraries; uses jQuery (bundled with WP)
- **Self-Hosted** — All data stays on your server; no cloud services required
- **Docker Ready** — One-command deployment with `docker-compose up -d`

---

## Quick Start

### Prerequisites

- Docker & Docker Compose installed

### Deploy

```bash
git clone https://github.com/obydullah/lime-lpa-docker.git
cd lime-lpa-docker
docker-compose up -d
```

### Access

| Service            | URL                     | Credentials          |
| ------------------ | ----------------------- | -------------------- |
| WordPress + Plugin | `http://localhost:8090` | Admin login required |
| phpMyAdmin         | `http://localhost:8091` | `root` / `root`      |

### First Use

1. Open `http://localhost:8090/wp-admin`
2. Log in with your WordPress admin credentials
3. Navigate to **Accounting** in the sidebar menu
4. Create your first wallet (e.g., "Salary" with category "Income")
5. Add income/expense entries linked to your wallets

---

## Database Schema

All tables use the `lpa_` prefix and include soft-delete support (`deleted_at` column).

```
┌──────────────┐     ┌──────────────┐
│   wallets    │────<│   incomes    │
│  (categories)│     │  (amount,    │
│              │────<│   wallet_id) │
│              │     └──────┬───────┘
│              │            │
│              │     ┌──────┴────────┐
│              │     │  cashbook     │
│              │     │  (auto ledger)│
│              │     └──────┬────────┘
│              │            │
│              │     ┌──────┴────────┐
└──────────────┘     │   expenses    │
                     │  (amount,     │
                     │   wallet_id)  │
                     └───────────────┘

  ┌──────────────┐    ┌───────────────────┐
  │  activities  │    │  configurations   │
  │  (audit log) │    │  (key-value store)│
  └──────────────┘    └───────────────────┘
```

### Table Summary

| Table                | Purpose                   | Key Columns                           |
| -------------------- | ------------------------- | ------------------------------------- |
| `lpa_wallets`        | Income/expense categories | name, category                        |
| `lpa_incomes`        | Income records            | fk_wallet_id, amount, currency        |
| `lpa_expenses`       | Expense records           | fk_wallet_id, amount, currency        |
| `lpa_cashbook`       | Auto-generated ledger     | in_amount, out_amount, reference_type |
| `lpa_activities`     | Audit trail               | type, name, ip_address                |
| `lpa_configurations` | Settings store            | name (unique), setting (JSON)         |

---

## Screenshots

> **Note:** Screenshots can be added after running the plugin. Recommended captures:
>
> - Dashboard with stats cards
> - Wallets list with add/edit modal
> - Income list with form modal
> - Expense list with form modal
> - Cashbook ledger view
> - Activities log

---

## API Actions

All operations go through a single AJAX endpoint: `wp_ajax_lpa_action`

### Wallets

| Action          | Method        | Description               |
| --------------- | ------------- | ------------------------- |
| `get_wallets`   | Read          | Fetch all active wallets  |
| `save_wallet`   | Create/Update | Create or update a wallet |
| `delete_wallet` | Soft Delete   | Mark wallet as deleted    |

### Incomes

| Action          | Method        | Description                           |
| --------------- | ------------- | ------------------------------------- |
| `get_incomes`   | Read          | Fetch all incomes with wallet name    |
| `save_income`   | Create/Update | Create/update income + cashbook entry |
| `delete_income` | Soft Delete   | Cascade soft-delete income + cashbook |

### Expenses

| Action           | Method        | Description                            |
| ---------------- | ------------- | -------------------------------------- |
| `get_expenses`   | Read          | Fetch all expenses with wallet name    |
| `save_expense`   | Create/Update | Create/update expense + cashbook entry |
| `delete_expense` | Soft Delete   | Cascade soft-delete expense + cashbook |

### Other

| Action           | Method | Description                        |
| ---------------- | ------ | ---------------------------------- |
| `get_cashbook`   | Read   | Fetch all ledger entries           |
| `get_activities` | Read   | Fetch last 50 activity log entries |
| `get_settings`   | Read   | Fetch all configuration entries    |

---

## Architecture

```
┌─────────────────────────────────────────────────────┐
│                   WordPress Admin                   │
│                                                     │
│  ┌───────────┐  ┌──────────┐  ┌──────────┐          │
│  │ Dashboard │  │ Wallets  │  │ Incomes  │  ...     │
│  │  (view)   │  │  (view)  │  │  (view)  │          │
│  └────┬──────┘  └────┬─────┘  └────┬─────┘          │
│       │              │             │                │
│       └──────────────┼─────────────┘                │
│                      │                              │
│              ┌───────┴────────┐                     │
│              │   script.js    │  jQuery AJAX layer  │
│              │   (SPA-like)   │                     │
│              └───────┬────────┘                     │
│                      │                              │
│              ┌───────┴────────┐                     │
│              │ ajax-handlers  │  Single router      │
│              │    .php        │  12 actions         │
│              └───────┬────────┘                     │
│                      │                              │
│              ┌───────┴────────┐                     │
│              │  database.php   │  Schema + helpers  │
│              └───────┬────────┘                     │
│                      │                              │
└──────────────────────┼──────────────────────────────┘
                       │
               ┌───────┴────────┐
               │  MySQL 8.0     │
               │  (6 tables)    │
               └────────────────┘
```

---

## Development

### Project Structure

```
wp-content/plugins/lime-personal-accounting/
├── lime-personal-accounting.php   # Entry point, hooks, assets
├── includes/
│   ├── database.php               # Schema creation, DB helpers
│   ├── admin-menu.php             # WordPress menu registration
│   ├── ajax-handlers.php          # All 12 AJAX CRUD handlers
│   ├── pages.php                  # Page controllers
│   └── views/                     # PHP templates (7 views)
│       ├── dashboard.php
│       ├── wallets.php
│       ├── incomes.php
│       ├── expenses.php
│       ├── cashbook.php
│       ├── activities.php
│       └── settings.php
└── assets/
    ├── css/style.css              # 426 lines of custom CSS
    └── js/script.js               # 351 lines of jQuery SPA
```

### Making Changes

1. Edit any file under `wp-content/plugins/lime-personal-accounting/`
2. Refresh the WordPress admin page
3. No rebuild required — volume-mounted for live reload

### Code Conventions

- **PHP functions**: All prefixed with `lpa_`
- **CSS classes**: All prefixed with `lpa-`
- **JS functions**: Exposed on `window` for inline handlers
- **Security**: Every AJAX call requires nonce + `manage_options` capability
- **DB access**: Always via `$wpdb` API (never raw PDO)
- **Soft deletes**: Every table has `deleted_at` column; never `DELETE FROM`

### Adding a New Feature

1. Add DB columns in `database.php` (use `dbDelta()`)
2. Add AJAX handler in `ajax-handlers.php`
3. Add router case in `lpa_ajax_router()`
4. Create/update view in `includes/views/`
5. Add JS functions in `script.js`
6. Register menu in `admin-menu.php` (if new page)

---

## Tech Stack

| Component       | Technology                |
| --------------- | ------------------------- |
| Language        | PHP 8.0+                  |
| CMS             | WordPress 6.0+            |
| Database        | MySQL 8.0                 |
| Frontend        | jQuery (WP-bundled)       |
| Styling         | Custom CSS (no framework) |
| Deployment      | Docker Compose            |
| DB Management   | phpMyAdmin                |
| Build Tools     | None                      |
| Package Manager | None                      |

---

## License

This project is licensed under the **GNU General Public License v2.0** — see the [LICENSE](https://www.gnu.org/licenses/old-licenses/gpl-2.0.html) file for details.

---

<p align="center">
  Built with ❤️ by <a href="https://obydullah.com">Obydullah</a>
</p>
