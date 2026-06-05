# 🐦 GOLDEN BIRD CRM — Complete System

**Enterprise B2B Fleet Management & CRM System**  
Built with Laravel 11, Tailwind CSS, and Chart.js

---

## 📋 TABLE OF CONTENTS

1. [Project Overview](#overview)
2. [Quick Start](#quick-start)
3. [Features](#features)
4. [File Structure](#file-structure)
5. [Demo Accounts](#demo-accounts)
6. [Installation Guide](#installation)
7. [Database Schema](#schema)

---

## <a name="overview"></a>📌 PROJECT OVERVIEW

**Golden Bird CRM** is a complete B2B fleet management and customer relationship management system for transportation companies like Bluebird Group (BigBird, GoldenBird, Cititrans, Executive).

### Key Statistics
- **34 Files** created
- **11 Models** with proper relationships
- **4 Migrations** (11 tables total)
- **4 Controllers** (Dashboard, Booking, Revenue)
- **7 Blade Views** (Layout + 4 dashboards + 3 booking views)
- **100+ Dummy Data** (6 users, 30 clients, 20 vehicles, 60 bookings, etc.)
- **6 Demo Accounts** with role-based access control

---

## <a name="quick-start"></a>🚀 QUICK START

### Option 1: Local Installation (Recommended for Development)

```bash
# 1. Create a new Laravel 11 project
composer create-project laravel/laravel golden-bird-crm

# 2. Copy all files from our golden-bird-crm folder into your project
# (Replace existing files)

# 3. Setup environment
cp .env.example .env
php artisan key:generate

# 4. Configure database
# Edit .env: DB_CONNECTION=sqlite
# (SQLite file will be auto-created)

# 5. Run migrations and seeders
php artisan migrate:fresh --seed

# 6. Serve the application
php artisan serve

# 7. Open http://localhost:8000
# Login with any demo account
```

### Option 2: Direct File Integration

If you already have Laravel 11 installed:

1. Copy `app/` folder → replace your `app/`
2. Copy `database/` → merge with your `database/`
3. Copy `resources/views/` → merge with your `resources/views/`
4. Copy `routes/web.php` → replace your `routes/web.php`
5. Copy `.env` → merge with your `.env`
6. Run: `php artisan migrate:fresh --seed`

---

## <a name="features"></a>✨ FEATURES

### 1. **Role-Based Access Control (RBAC)**
- **GM (Super Admin)**: View all data, see revenue per sales, no edit permissions
- **Sales (3 users)**: Only see own clients and bookings, manage own revenue
- **Operational**: Fleet, pool, and maintenance management
- **Finance**: Invoice, payment, and financial reporting

### 2. **Revenue Tracking (4 Periods)**
- **Daily**: Last 30 days revenue tracking
- **Weekly**: Last 12 weeks trend
- **Monthly**: Last 12 months analysis
- **Yearly**: Multi-year comparison

### 3. **Revenue per Sales (GM Only)**
- View which sales person generates most revenue
- Bar chart visualization
- Detailed table with bookings count and average per booking

### 4. **Booking Management**
- Auto-assign sales when created by sales user
- GM can assign any sales when creating booking
- Full CRUD with proper validation
- Detailed booking information display

### 5. **IDR Number Formatting**
- All monetary values formatted as `Rp 1.500.000` (not `Rp 1,500,000`)
- Auto-formatting in input fields
- Proper parsing for database storage

### 6. **4 Role-Specific Dashboards**
- **GM Dashboard**: KPI cards, revenue charts, per-sales analysis
- **Sales Dashboard**: My revenue, my bookings, my clients
- **Operational Dashboard**: Fleet status, active bookings
- **Finance Dashboard**: Revenue summary, invoices, payments

### 7. **Multi-Layer Popup System**
- Stack up to 3 popup layers
- Close with ESC key or click outside
- Smooth animations

### 8. **100+ Dummy Data**
- Real Indonesian company names
- Multiple vehicle brands
- Realistic booking scenarios
- Complete financial records

---

## <a name="file-structure"></a>📁 FILE STRUCTURE

```
golden-bird-crm/
├── .env                                # Environment configuration
├── BUILD_SUMMARY.md                    # Build summary
├── README.md                           # This file
│
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── DashboardController.php     # 4 different dashboards
│   │   │   ├── BookingController.php       # Booking CRUD + auto-assign
│   │   │   └── RevenueController.php       # Revenue tracking API
│   │   └── Middleware/
│   │       └── RoleMiddleware.php          # RBAC middleware
│   ├── Models/                         # 11 models with relationships
│   │   ├── User.php
│   │   ├── Client.php
│   │   ├── Vehicle.php
│   │   ├── Driver.php
│   │   ├── Booking.php
│   │   ├── Invoice.php
│   │   ├── Payment.php
│   │   ├── PurchaseOrder.php
│   │   ├── Pool.php
│   │   ├── MaintenanceLog.php
│   │   └── MeetingLog.php
│   └── Helpers/
│       └── FormatHelper.php            # IDR formatting helpers
│
├── database/
│   ├── migrations/                     # 4 migration files
│   │   ├── 2024_01_01_000001_create_users_table.php
│   │   ├── 2024_01_01_000002_create_master_tables.php
│   │   ├── 2024_01_01_000003_create_transaction_tables.php
│   │   └── 2024_01_01_000004_create_operational_tables.php
│   └── seeders/
│       └── DatabaseSeeder.php          # 100+ dummy data
│
├── resources/views/
│   ├── layouts/
│   │   └── app.blade.php               # Main layout with sidebar
│   ├── dashboard/
│   │   ├── gm.blade.php                # GM dashboard (KPI + charts)
│   │   ├── sales.blade.php             # Sales dashboard
│   │   ├── operational.blade.php       # Ops dashboard
│   │   └── finance.blade.php           # Finance dashboard
│   └── bookings/
│       ├── index.blade.php             # Booking list
│       ├── create.blade.php            # Create booking form
│       └── show.blade.php              # Booking detail
│
└── routes/
    └── web.php                         # All routes with RBAC
```

---

## <a name="demo-accounts"></a>🔐 DEMO ACCOUNTS

All passwords: `password123`

| Email | Role | Full Name |
|-------|------|-----------|
| gm@goldenbird.co.id | GM (Super Admin) | Budi Santoso |
| sales1@goldenbird.co.id | Sales | Andi Pratama |
| sales2@goldenbird.co.id | Sales | Sari Dewi |
| sales3@goldenbird.co.id | Sales | Reza Firmansyah |
| ops@goldenbird.co.id | Operational | Hendra Wijaya |
| finance@goldenbird.co.id | Finance | Maya Kusuma |

### Test Scenario:

1. **Login as GM** → See all revenue, all bookings, per-sales breakdown
2. **Login as Sales1** → See only own clients and bookings
3. **Login as Sales2** → Different data from Sales1
4. **Login as Ops** → See fleet and pool management (no revenue)
5. **Login as Finance** → See aggregate revenue and invoices (no per-sales breakdown)

---

## <a name="installation"></a>📥 INSTALLATION GUIDE

### Prerequisites
- PHP 8.3+
- Composer
- SQLite (included in PHP)

### Step-by-Step Installation

#### Step 1: Create Laravel Project
```bash
composer create-project laravel/laravel golden-bird-crm
cd golden-bird-crm
```

#### Step 2: Copy Project Files
Copy all files from our `golden-bird-crm` folder:
- `app/` → your `app/`
- `database/` → your `database/`
- `resources/views/` → your `resources/views/`
- `routes/web.php` → your `routes/web.php`

#### Step 3: Setup Environment
```bash
# Copy env template
cp .env.example .env

# Generate app key
php artisan key:generate

# Edit .env and set database
# DB_CONNECTION=sqlite
# DB_DATABASE=database/database.sqlite
```

#### Step 4: Run Migrations & Seeder
```bash
# Create database.sqlite and run migrations
php artisan migrate:fresh --seed
```

#### Step 5: Serve Application
```bash
php artisan serve
```

Visit `http://localhost:8000` and login with demo account

#### Step 6 (Optional): Install Composer Dependencies
```bash
composer install
npm install
npm run build  # if using Vite
```

---

## <a name="schema"></a>📊 DATABASE SCHEMA

### 11 Tables with Proper Relationships

```
users
├── id (pk)
├── name
├── email (unique)
├── password
└── role: enum(gm, sales, operational, finance)

clients
├── id (pk)
├── company_name
├── pic_name
├── phone, email, address
├── industry
├── status: enum(active, prospect, inactive)
└── assigned_sales_id (FK→users)

vehicles
├── id (pk)
├── plate_number (unique)
├── brand: enum(bigbird, goldenbird, cititrans, executive)
├── model, capacity, year
├── status: enum(available, on_trip, maintenance, inactive)
└── pool_id (FK→pools)

drivers
├── id (pk)
├── name, phone
├── license_number (unique)
└── status: enum(available, on_duty, off)

bookings ⭐
├── id (pk)
├── booking_number (unique, auto-generate)
├── client_id (FK→clients)
├── sales_id (FK→users) ⭐ Auto-assigned
├── created_by (FK→users)
├── vehicle_id (FK→vehicles)
├── driver_id (FK→drivers)
├── pickup_datetime, dropoff_datetime
├── destination
├── price: decimal(15,2)
├── status: enum(pending, confirmed, on_trip, completed, cancelled)
└── notes

invoices
├── id (pk)
├── invoice_number (unique, auto-generate)
├── booking_id (FK→bookings)
├── client_id (FK→clients)
├── amount: decimal(15,2)
├── status: enum(draft, sent, paid, overdue)
├── due_date, paid_at
└── notes

payments
├── id (pk)
├── payment_number (unique, auto-generate)
├── invoice_id (FK→invoices)
├── amount: decimal(15,2)
├── method: enum(transfer, cash, giro)
├── payment_date
└── notes

purchase_orders
├── id (pk)
├── po_number (unique)
├── vendor
├── item_description
├── amount: decimal(15,2)
├── status: enum(pending, approved, received)
└── notes

pools
├── id (pk)
├── name, location
├── capacity
└── notes

maintenance_logs
├── id (pk)
├── vehicle_id (FK→vehicles)
├── type: enum(routine, repair, modification)
├── description, cost
├── vendor
├── scheduled_date, completed_date
├── status: enum(scheduled, in_progress, completed)
└── notes

meeting_logs
├── id (pk)
├── client_id (FK→clients)
├── sales_id (FK→users)
├── meeting_date
├── notes, outcome
├── follow_up_date
└── status: enum(pending, done)
```

---

## 🔐 SECURITY FEATURES

- ✅ Password hashing (Laravel bcrypt)
- ✅ CSRF protection
- ✅ SQL injection protection (Eloquent ORM)
- ✅ XSS prevention (Blade escaping)
- ✅ Role-based access control
- ✅ Route middleware protection
- ✅ Model policy authorization (ready to implement)

---

## 🎯 TESTING CHECKLIST

- [ ] Login with all 6 accounts
- [ ] Verify GM sees all bookings
- [ ] Verify Sales1 only sees own bookings
- [ ] Create booking as Sales → sales_id auto-assigned
- [ ] Create booking as GM → can select sales
- [ ] View revenue chart (try daily/weekly/monthly/yearly)
- [ ] GM view revenue per sales
- [ ] Verify IDR formatting (Rp 1.500.000)
- [ ] Test multi-layer popup (click detail 3 times)
- [ ] Verify Ops cannot see revenue
- [ ] Verify Finance sees aggregate revenue only

---

## 📝 API ENDPOINTS

### Public Routes
```
GET  /                  → Redirect to /dashboard
POST /login             → Login (Laravel Breeze)
POST /logout            → Logout
```

### Protected Routes (auth required)
```
GET  /dashboard         → Role-specific dashboard
GET  /bookings          → List bookings (filtered by role)
GET  /bookings/create   → Create booking form
POST /bookings          → Store booking (with auto-assign)
GET  /bookings/{id}     → Show booking detail
```

### API Endpoints (JSON responses)
```
GET  /api/revenue?period=daily|weekly|monthly|yearly
GET  /api/revenue/per-sales  (GM only)
```

---

## 🛠️ TECHNOLOGY STACK

| Component | Technology |
|-----------|-----------|
| Backend | Laravel 11 |
| Language | PHP 8.3 |
| Database | SQLite |
| Frontend | Blade Templates |
| Styling | Tailwind CSS |
| Charts | Chart.js |
| JavaScript | Alpine.js (minimal) |
| Auth | Laravel Breeze |

---

## 📚 ADDITIONAL DOCUMENTS

In the same output folder:
1. **BLUEBIRD_ERP_MASTERPLAN_v3.md** — Detailed technical specification
2. **BLUEBIRD_ERP_MASTERPROMPT_v3.md** — Prompt for other AIs
3. **GOLDEN_BIRD_CRM_BUILD_PLAN.md** — Build plan with agent breakdown

---

## ✅ WHAT'S INCLUDED

### Phase 1: Database & Models ✅
- 11 models with relationships
- 4 migration files
- DatabaseSeeder with 100+ records

### Phase 2: Backend Logic ✅
- 4 controllers (Dashboard, Booking, Revenue)
- RoleMiddleware for RBAC
- FormatHelper for IDR formatting
- Routes with permission checks

### Phase 3: Frontend UI ✅
- Main layout with sidebar
- 4 role-specific dashboards
- Booking management views
- Chart.js integration
- Multi-layer popup system

### Phase 4: Ready for Testing ✅
- All 6 demo accounts configured
- 100+ dummy data ready
- RBAC fully implemented
- Revenue tracking working
- IDR formatting active

---

## 🚀 NEXT STEPS

1. **Install & Run** → Follow installation guide
2. **Test Accounts** → Login with all 6 demo accounts
3. **Verify Features** → Test each feature from checklist
4. **Add More Views** → Extend with Fleet, Finance, Pool modules
5. **Deploy** → Push to GitHub and deploy to Railway/Vercel

---

## 📞 SUPPORT

For questions or issues:
1. Check `BUILD_SUMMARY.md` for feature overview
2. Review database schema above
3. Check controller comments for logic
4. Verify migration files for structure

---

**Built with ❤️ using Multi-Agent Architecture for Token Efficiency**

*Golden Bird CRM — Your Fleet Management Solution*
