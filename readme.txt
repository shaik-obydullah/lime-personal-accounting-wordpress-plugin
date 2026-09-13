=== Obydullah Personal Accounting ===
Contributors: obydullah
Tags: accounting, income, expense, wallet, cashbook
Requires at least: 6.0
Tested up to: 7.1
Requires PHP: 8.0
Stable tag: 1.0.0
License: GPL-2.0-or-later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Track personal income, expenses, wallets, and automatic cashbook ledgers - all inside your WordPress admin.

== Description ==

Obydullah Personal Accounting is a self-contained, admin-only accounting dashboard for WordPress. Track your personal income and expenses, organize them into wallets (income/expense categories), and get an automatic cashbook ledger - all without leaving your WordPress admin.

= Features =

* Dashboard with real-time income, expense, and balance overview
* Wallet management with income and expense categories
* Income and expense tracking with amount, description, and currency
* Automatic cashbook ledger generated from every transaction
* Activity log with audit trail
* Soft deletes - data is never physically removed
* Zero external dependencies, fully self-hosted

= How it works =

1. Create wallets such as "Salary" (income) or "Food" (expense).
2. Log your income and expense transactions against those wallets.
3. Every transaction automatically appears in the Cashbook ledger.
4. The Activities page keeps an audit trail of everything you do.

== Installation ==

1. Upload the `obydullah-personal-accounting` folder to the `/wp-content/plugins/` directory, or install the plugin through the WordPress Plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress.
3. The plugin creates its database tables automatically on activation.
4. Open the 'Accounting' menu in the WordPress sidebar to get started.

== Frequently Asked Questions ==

= Is my financial data stored anywhere else? =

No. All data is stored in your own WordPress database only. There are no external calls, telemetry, or cloud services.

= What personal data does the plugin store? =

For administrators only, the plugin records the WordPress user ID and IP address in its activity log as an audit trail. See the WordPress Privacy data export and erase tools if you need to review or remove this data.

= Can other users see my accounting data? =

No. All accounting pages and actions require the `manage_options` capability, so only administrators can view or edit data.

= Why does the Cashbook exist? =

The cashbook is a single source of truth for every financial movement. Each added income or expense automatically creates a corresponding ledger entry.

== Screenshots ==

1. The Dashboard with total income, expense, and balance overview.
2. The Wallets page with add/edit modal.
3. The Incomes page with add/edit modal.
4. The Expenses page with add/edit modal.
5. The Cashbook ledger page.
6. The Activities page.

== Changelog ==

= 1.0.0 =
* Initial release.
* Dashboard, wallet, income, expense, cashbook, activity, and settings pages.
* Automatic cashbook ledger and activity logging.
* Soft-delete on all data.