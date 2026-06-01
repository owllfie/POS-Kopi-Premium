# 📄 Page List — Restaurant POS System

> Defines all pages in the system, their purpose, access rights, and key features.

---

## Table of Contents

1. [Role Access Summary](#1-role-access-summary)
2. [Page Details](#2-page-details)
   - [Login Page](#login-page)
   - [Halaman Menu](#halaman-menu)
   - [Dashboard Admin](#dashboard-admin)
   - [Dashboard Manager](#dashboard-manager)
   - [Dashboard Kasir](#dashboard-kasir)
   - [Daftar Pesanan](#daftar-pesanan)
   - [Halaman Pembayaran](#halaman-pembayaran)
   - [Laporan](#laporan)
   - [Riwayat Transaksi](#riwayat-transaksi)
   - [Kelola Users](#kelola-users)
   - [Kelola Menu](#kelola-menu)
   - [Kelola Kategori](#kelola-kategori)
   - [Kelola Meja](#kelola-meja)
   - [Kelola Shift](#kelola-shift)
   - [Kelola Akses](#kelola-akses)
   - [Log Aktivitas](#log-aktivitas)
   - [Web Setting](#web-setting)
   - [Backup Database](#backup-database)

---

## 1. Role Access Summary

| Page | Guest (Customer) | Kasir | Chef | Manager | Admin | Superadmin |
|------|:---:|:---:|:---:|:---:|:---:|:---:|
| Login Page | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ |
| Halaman Menu | ✅ | — | — | — | — | — |
| Dashboard Admin | — | — | — | — | ✅ | ✅ |
| Dashboard Manager | — | — | — | ✅ | — | — |
| Dashboard Kasir | — | ✅ | — | — | — | — |
| Daftar Pesanan | — | ✅ | ✅ | — | ✅ | ✅ |
| Halaman Pembayaran | — | ✅ | — | — | ✅ | ✅ |
| Laporan | — | — | — | ✅ | ✅ | ✅ |
| Riwayat Transaksi | — | — | — | ✅ | ✅ | ✅ |
| Kelola Users | — | — | — | — | ✅ | ✅ |
| Kelola Menu | — | — | — | — | ✅ | ✅ |
| Kelola Kategori | — | — | — | — | ✅ | ✅ |
| Kelola Meja | — | — | — | — | ✅ | ✅ |
| Kelola Shift | — | — | — | — | ✅ | ✅ |
| Kelola Akses | — | — | — | — | — | ✅ |
| Log Aktivitas | — | — | — | — | — | ✅ |
| Web Setting | — | — | — | — | — | ✅ |
| Backup Database | — | — | — | — | — | ✅ |

> **Notes:**
> - **Guest (Customer)** is not a registered user. Access to Halaman Menu is via QR code token only, with no login required. All other pages are blocked.
> - **Superadmin** accounts are hidden from the Kelola Users page (not visible to Admin).
> - **Admin** and **Superadmin** share the same Dashboard (Dashboard Admin).

---

## 2. Page Details

---

### Login Page

- **Route:** `/login`
- **Access:** All roles (redirected to their dashboard after login; guests are never directed here)
- **Description:** Standard credential-based login for all internal users (Kasir, Chef, Manager, Admin, Superadmin).

**Features:**
- Email/username + password form
- Validation with error messages
- On successful login, redirects to role-specific dashboard
- Already-authenticated users are redirected away from this page
- No "Register" option (users are created by Admin/Superadmin)

---

### Halaman Menu

- **Route:** `/menu/{qrcode_token}`
- **Access:** Guest (Customer) — no authentication required
- **Description:** The public-facing menu page that customers land on after scanning the QR code at their table. The table number is automatically detected from the QR token.

**Features:**
- Displays all menu items grouped by kategori
- Items with status `habis` are shown as greyed out / unavailable
- Customer can add items to cart, set quantity, and add a note per item
- Cart summary shown before submitting
- On submit, order is saved with `id_meja` auto-detected from QR token
- Order confirmation screen shown after submission
- No login, no account needed — session/token-based only
- All other routes inaccessible to guest (middleware block)

---

### Dashboard Admin

- **Route:** `/dashboard`
- **Access:** Admin, Superadmin
- **Description:** The main overview page for Admin and Superadmin. Focuses on revenue trends and key operational metrics.

**Features:**
- **Revenue Trend Chart** — Line or bar chart showing daily revenue for the current period (default: last 7 days). Toggle options: 7 days / 30 days / custom range.
- **Summary Cards:**
  - Total Pendapatan Hari Ini
  - Total Transaksi Hari Ini
  - Jumlah Meja Aktif (status: terisi)
  - Total Menu Tersedia
- **Top Menu Items** — List of best-selling items this week
- **Recent Transactions** — Last 5 completed orders with table, total, and method
- Quick navigation links to management pages

---

### Dashboard Manager

- **Route:** `/dashboard`
- **Access:** Manager
- **Description:** Revenue-focused overview for the Manager role. Shows aggregated sales data across time periods.

**Features:**
- **Revenue Trend Chart** — Line chart with toggle: Harian / Mingguan / Bulanan
- **Summary Cards:**
  - Pendapatan Hari Ini
  - Pendapatan Minggu Ini
  - Pendapatan Bulan Ini
  - Total Transaksi Bulan Ini
- Quick links to Laporan and Riwayat Transaksi

---

### Dashboard Kasir

- **Route:** `/dashboard`
- **Access:** Kasir
- **Description:** Operational dashboard for the cashier. Acts as a **Laporan Harian** in progress — showing today's shift performance at a glance.

**Features:**
- **Today's Progress (Laporan Harian Live):**
  - Total transaksi hari ini
  - Total pendapatan hari ini
  - Breakdown: Cash vs QRIS
  - Kas di tangan (entered at shift start/end) vs total cash transaksi → shows selisih (discrepancy)
- **Shift Status Banner** — Shows current shift start time and duration
- **Recent Orders** — Last few orders processed in this shift (table, items, total, time)
- Quick link to Daftar Pesanan and Halaman Pembayaran

---

### Daftar Pesanan

- **Route:** `/pesanan`
- **Access:** Kasir, Chef, Admin, Superadmin
- **Description:** Shows the live queue of incoming orders. What each role sees differs slightly.

**Features (Kasir / Admin / Superadmin view):**
- List of all active orders with: Table number, items ordered, total, order time, status
- Filter by status: Semua / Menunggu / Dimasak / Selesai
- Button to open Halaman Pembayaran for a specific order
- Notification indicator for new orders

**Features (Chef view):**
- Displays only `detail_pesanan` items with status `menunggu` or `dimasak`
- Grouped by table number
- Action buttons per item:
  - `Mulai Masak` → updates status to `dimasak`
  - `Selesai` → updates status to `selesai`
- Auto-refresh every 30 seconds (or real-time via WebSocket)
- No access to payment or financial info

---

### Halaman Pembayaran

- **Route:** `/pesanan/{id}/bayar`
- **Access:** Kasir, Admin, Superadmin
- **Description:** The payment processing page for a specific order. Opened from Daftar Pesanan.

**Features:**
- Order summary: table number, list of items, subtotals, pajak, total
- Select payment method: Cash / QRIS
- For Cash: enter nominal received → system calculates kembalian (change)
- For QRIS: show QR code for customer to scan (static or dynamic)
- Confirm payment → creates `pesanan` record, updates `meja` status to `kosong`
- Print/generate struk (receipt) after payment confirmed
- Soft-deletes any pending draft if cancelled

---

### Laporan

- **Route:** `/laporan`
- **Access:** Manager, Admin, Superadmin
- **Description:** Aggregated financial reports with time-period filters.

**Features:**
- **Laporan Harian** — Summary for a selected date: total orders, revenue, cash, QRIS, selisih kas
- **Laporan Mingguan** — Week-over-week summary: daily breakdown table + chart
- **Laporan Bulanan** — Month view: weekly or daily breakdown, growth percentage
- Filter by: tanggal / minggu / bulan, kasir, metode pembayaran
- Export to PDF or CSV

---

### Riwayat Transaksi

- **Route:** `/transaksi`
- **Access:** Manager, Admin, Superadmin
- **Description:** Full, searchable log of every completed transaction.

**Features:**
- Table columns: Kode Struk, No. Meja, Kasir, Items (count), Metode Pembayaran, Total, Pajak, Waktu
- Search by kode struk or meja
- Filter by: date range, metode pembayaran, kasir
- Click a row to view full order detail (items ordered, notes, etc.)
- Soft-deleted transactions can be viewed in a separate Trash tab (Admin/Superadmin only)
- Export to PDF or CSV

---

### Kelola Users

- **Route:** `/users`
- **Access:** Admin, Superadmin
- **Description:** Manage all internal user accounts (Kasir, Chef, Manager, Admin). Superadmin accounts are hidden from this page.

**Features:**
- Table: Username, Email, Role, Status, Created At, Actions
- **Create**: Add new user with role assignment
- **Edit**: Update username, email, role, password
- **Soft Delete**: Deactivate a user (they can no longer log in)
- **Trash Tab**: View soft-deleted users → Restore or Permanently Delete
- Superadmin role is excluded from the role dropdown and from the user list

---

### Kelola Menu

- **Route:** `/menu`
- **Access:** Admin, Superadmin
- **Description:** Manage all food and drink items available for ordering.

**Features:**
- Table: Foto, Nama Menu, Kategori, Harga, Status, Actions
- **Create**: Add new menu item with photo upload, category, price, status
- **Edit**: Update any field including photo
- **Toggle Status**: Quickly switch between `tersedia` / `habis` without editing the full form
- **Soft Delete**: Remove from active menu (item hidden from Halaman Menu)
- **Trash Tab**: View deleted items → Restore or Permanently Delete

---

### Kelola Kategori

- **Route:** `/kategori`
- **Access:** Admin, Superadmin
- **Description:** Manage menu categories (e.g., Makanan, Minuman, Snack).

**Features:**
- Table: Nama Kategori, Jumlah Menu, Actions
- **Create**: Add new category
- **Edit**: Rename category
- **Soft Delete**: Hide category (also hides associated menu items from Halaman Menu)
- **Trash Tab**: Restore or Permanently Delete

---

### Kelola Meja

- **Route:** `/meja`
- **Access:** Admin, Superadmin
- **Description:** Manage restaurant tables and their QR codes.

**Features:**
- Table: No. Meja, Status (Kosong/Terisi), QR Token, Actions
- **Create**: Add a new table — system auto-generates a unique `qrcode_token`
- **View QR**: Display or download the QR code image for a table (for printing)
- **Regenerate QR**: Generate a new token (invalidates old QR code)
- **Edit**: Update table number
- **Soft Delete**: Remove table from active use
- **Trash Tab**: Restore or Permanently Delete

---

### Kelola Shift

- **Route:** `/shift`
- **Access:** Admin, Superadmin
- **Description:** View and manage shift records for all Kasir.

**Features:**
- Table: Kasir, Jam Mulai, Jam Selesai, Cash Masuk, QRIS Masuk, Total Masuk, Actions
- **View Detail**: See all transactions within a specific shift
- **Edit**: Correct shift times or cash entries if needed
- **Soft Delete**: Archive a shift record
- **Trash Tab**: Restore or Permanently Delete
- Kasir-facing shift start/end actions are triggered from Dashboard Kasir

---

### Kelola Akses

- **Route:** `/akses`
- **Access:** Superadmin only
- **Description:** Configure which modules each role can access. Controls the `aksess` table.

**Features:**
- Role selector (Kasir, Chef, Manager, Admin)
- Grid of all modules with toggle switches (Allowed / Denied)
- Changes take effect immediately on next page load for affected users
- Cannot modify Superadmin's own access from this page

---

### Log Aktivitas

- **Route:** `/log`
- **Access:** Superadmin only
- **Description:** Audit trail of all significant actions performed in the system.

**Features:**
- Table: User, Aktivitas, Detail, IP Address, Waktu
- Filter by: user, action type, date range
- Read-only — no editing or deleting
- Logged events include: login, logout, create, update, soft delete, restore, permanent delete, backup

---

### Web Setting

- **Route:** `/setting`
- **Access:** Superadmin only
- **Description:** Global configuration for the restaurant's POS system.

**Features:**
- Nama Restoran
- Logo upload
- Persentase Pajak (auto-applied to all orders)
- Footer text on struk / receipt
- Application theme (light/dark or brand color)
- Save changes → reflected system-wide immediately

---

### Backup Database

- **Route:** `/backup`
- **Access:** Superadmin only
- **Description:** Manage database backups for disaster recovery.

**Features:**
- **Backup Now**: Triggers a full database dump (`.sql` file)
- **Download**: Download the latest or any previous backup file
- **Backup History**: Table of past backups — filename, size, date, actions
- **Delete Backup**: Remove old backup files from storage
- Last backup timestamp shown prominently on the page
