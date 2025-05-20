# Restaurant Management System 🍽️

A comprehensive restaurant management platform for seamless reservations, order processing, inventory control, and multi-branch operations. Built to streamline dine-in, takeaway, kitchen workflows, and customer engagement with real-time tracking.

![System Overview](https://example.com/path-to-system-screenshot.jpg) <!-- Replace with actual image URL -->

## ✨ Key Features

### 1. Customer & Reservation Management
- Phone number-based tracking for registered/unregistered customers
- Multi-channel reservations (online, in-call, walk-in) with dynamic fees/cancellation policies
- Automated waitlist with SMS/email notifications
- Interactive table map (reserved/occupied/dirty/open statuses)

### 2. Order Processing
- Dine-in (requires reservation) and takeaway (scheduled/demand-driven) orders
- Auto-reservation for walk-in dine-in orders via QR/waiter input
- KOT generation, kitchen station splits, and real-time order status tracking

### 3. Kitchen & Bar Workflows
- Chef task allocation with inventory checks
- Critical alerts for restocks
- Bar management (bottles/custom drinks) with daily inventory reconciliation

### 4. Payments & Billing
- Supports cash, cards, mobile apps, QR codes
- Automated discounts (loyalty/promos), service charges
- Multi-branch billing capabilities

### 5. Inventory & Analytics
- Real-time inventory sync across branches/head office
- Expiry/wastage alerts
- Automated restock requests and purchase order handling
- Comprehensive business reports (sales, inventory, customer preferences)

### 6. Staff & Admin Tools
- Role-based access control (waiters, chefs, cashiers, admins)
- Centralized management console

## 🛠️ Technology Stack

| Component          | Technology                          |
|--------------------|-------------------------------------|
| Backend            | Laravel 12 (PHP 8.3+)               |
| Admin Panel        | Laravel Nova 4.x                    |
| Database           | PostgreSQL 16 + TimescaleDB         |
| Frontend           | Blade + Tailwind CSS 3.4            |
| Real-time Features | Laravel Echo + Pusher               |
| Deployment         | Docker/Laravel Forge                |

## 🚀 Installation Guide

### Prerequisites
- PHP 8.3+
- PostgreSQL 16
- Composer 2.6+
- Node.js 20+
- Laravel Nova license

### Setup Instructions

1. **Clone repository**
   ```bash
   git clone https://github.com/Unitec-Software-Solutions/Restaurant-Management-System.git
   cd Restaurant-Management-System
   ```

2. **Install dependencies**
   ```bash
   composer i
   npm i
   ```

3. **Configure environment**
   ```bash
   cp .env.example .env
   nano .env  # Set DB credentials and Nova license
   php artisan key:generate
   ```

4. **Database setup**
   ```bash
   php artisan migrate:fresh --seed
   ```
   ```bash
   php artisan optimize:clear
   ```

5. **Build assets**
   ```bash
   npm run build
   ```

6. **Start development server**
   ```bash
   php artisan serve
   ```


## 📊 Demo Access
Demo credentials for testing:
- **Admin Panel**: `admin@rms.com` / `admin123`
- **Manager Portal**: `manager@example.com` / `Manager@1234`
- **Staff Portal**: `staff@example.com` / `Staff@1234`

## 📜 License
This project is proprietary software developed by [Unitec Software Solutions](https://www.unitecsoftware.lk). All rights reserved.

## 🤝 Contributing
- Email: contact@unitecsoftware.lk
- Phone: ~snip~

## 📞 Support
For technical support:
- [Open a support ticket](https://support.unitecsoftware.lk)
- Email: support@unitecsoftware.lk
- Emergency line: ~snip~

---

> **Note**: ~snip~
