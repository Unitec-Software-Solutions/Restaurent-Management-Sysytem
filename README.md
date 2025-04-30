Restaurant-Management-System
A comprehensive restaurant management platform for seamless reservations, order processing, inventory control, and multi-branch operations. Built to streamline dine-in, takeaway, kitchen workflows, and customer engagement with real-time tracking.

✨ Features
1. Customer & Reservation Management
Phone number-based tracking for registered/unregistered customers.

Multi-channel reservations (online, in-call, walk-in) with dynamic fees/cancellation policies.

Automated waitlist with SMS/email notifications.

2. Order Processing
Dine-in (requires reservation) and takeaway (scheduled/demand-driven) orders.

Auto-reservation for walk-in dine-in orders via QR/waiter input.

KOT generation, kitchen station splits, and real-time order status tracking.

3. Kitchen & Bar Workflows
Chef task allocation with inventory checks and critical alerts for restocks.

Bar management (bottles/custom drinks) with daily inventory reconciliation.

4. Payments & Billing
Supports cash, cards, mobile apps, QR codes, and more.

Automated discounts (loyalty/promos), service charges, and multi-branch billing.

5. Inventory & Analytics
Real-time inventory sync across branches/head office with expiry/wastage alerts.

Automated restock requests and purchase order handling.

6. Staff & Admin Tools
Role-based access control (waiters, chefs, cashiers, admins).

7. Interactive table map (reserved/occupied/dirty/open statuses).
Business reports (sales, inventory, customer preferences).

##Tech Highlights
Component Technology Backend Laravel 12 (PHP 8.3+) Admin Panel Laravel Nova 4.x Database PostgreSQL 16 + TimescaleDB Frontend Blade + Tailwind CSS 3.4 Real-time Laravel Echo + Pusher Deployment Docker/Laravel Forge

🚀 Installation
Requirements PHP 8.3+

PostgreSQL 16

Composer 2.6+

Node.js 20+

Laravel Nova license

Clone repository
git clone https://github.com/Unitec-Software-Solutions/Restaurant-Management-System.git cd Restaurant-Management-System

Install dependencies
composer install --optimize-autoloader npm install --legacy-peer-deps

Configure environment
cp .env.example .env nano .env # Set DB credentials and Nova license

Database setup
php artisan migrate:fresh --seed php artisan optimize:clear

Build assets
npm run build

Start development
php artisan serve