# 🗄️ Database Detail — Restaurant POS System

> Based on the entity-relationship class diagram. All tables use soft delete via `deleted_at` unless noted otherwise.

---

## Table of Contents

1. [Entity Relationship Overview](#1-entity-relationship-overview)
2. [Tables](#2-tables)
   - [role](#role)
   - [users](#users)
   - [aksess](#aksess)
   - [shift](#shift)
   - [kategori](#kategori)
   - [menu](#menu)
   - [meja](#meja)
   - [pesanan](#pesanan)
   - [detail_pesanan](#detail_pesanan)
   - [activity_log](#activity_log)
   - [history_update](#history_update)
3. [Relationships Summary](#3-relationships-summary)
4. [Soft Delete Policy](#4-soft-delete-policy)
5. [Key Enums](#5-key-enums)

---

## 1. Entity Relationship Overview

```
role ──────────< users >──────────── shift
  │                │
  └──────────< aksess        users >──── pesanan >──── detail_pesanan
                                │                           │
                           activity_log              menu >─┘
                                                      │
                           history_update         kategori
                           
meja ──────────────────────────────────────────< pesanan
```

- `role` → `users`: one role has many users
- `role` → `aksess`: one role has many access rules
- `users` → `shift`: one user (kasir) has many shifts
- `users` → `pesanan`: one user (kasir) processes many orders
- `users` → `activity_log`: one user generates many log entries
- `meja` → `pesanan`: one table has many orders over time
- `pesanan` → `detail_pesanan`: one order has many detail lines
- `menu` → `detail_pesanan`: one menu item appears in many order details
- `kategori` → `menu`: one category has many menu items

---

## 2. Tables

---

### `role`

Stores all available user roles in the system.

| Column | Type | Constraint | Description |
|--------|------|-----------|-------------|
| `id_role` | int(11) | PK | Primary key |
| `role` | varchar(50) | NOT NULL | Role name (e.g., `superadmin`, `admin`, `manager`, `kasir`, `chef`) |

**Notes:**
- No soft delete — roles are static system configuration
- The `superadmin` role is a reserved system role and cannot be assigned via the Kelola Users page

---

### `users`

All internal system users. Does not include customers (they are guests).

| Column | Type | Constraint | Description |
|--------|------|-----------|-------------|
| `id_user` | int(11) | PK | Primary key |
| `username` | varchar(255) | NOT NULL | Display name / login name |
| `email` | varchar(255) | NOT NULL, UNIQUE | Login email |
| `password` | varchar(80) | NOT NULL | Hashed password (bcrypt) |
| `id_role` | int(11) | FK → `role.id_role` | Assigned role |
| `created_at` | timestamp | | Record creation time |
| `updated_at` | timestamp | | Last update time |
| `deleted_at` | timestamp | NULLABLE | Soft delete timestamp |

**Notes:**
- Superadmin accounts (`id_role` = superadmin) are excluded from the Kelola Users listing query
- A soft-deleted user cannot log in (check `deleted_at IS NULL` on authentication)
- Password must be stored hashed, never plain text

---

### `aksess`

Defines which modules each role is allowed to access. Controls page-level permissions.

| Column | Type | Constraint | Description |
|--------|------|-----------|-------------|
| `id_akses` | int(11) | PK | Primary key |
| `id_role` | int(11) | FK → `role.id_role` | Target role |
| `modul` | varchar(255) | NOT NULL | Module/page identifier (e.g., `kelola_menu`, `laporan`) |
| `allowed` | enum('0', '1') | NOT NULL | `1` = access granted, `0` = access denied |
| `created_at` | timestamp | | |
| `updated_at` | timestamp | | |
| `deleted_at` | timestamp | NULLABLE | Soft delete |

**Notes:**
- Managed via the Kelola Akses page (Superadmin only)
- Checked on every page load via middleware
- Superadmin's own access entries are not editable from the UI

---

### `shift`

Records each cashier shift including cash received.

| Column | Type | Constraint | Description |
|--------|------|-----------|-------------|
| `id_shift` | int(11) | PK | Primary key |
| `id_user` | int(11) | FK → `users.id_user` | Kasir on duty |
| `jam_mulai` | timestamp | NOT NULL | Shift start time |
| `jam_selesai` | timestamp | NULLABLE | Shift end time (null = shift still active) |
| `cash_masuk` | int(11) | DEFAULT 0 | Total cash payments received this shift |
| `qris_masuk` | int(11) | DEFAULT 0 | Total QRIS payments received this shift |
| `total_masuk` | int(11) | DEFAULT 0 | `cash_masuk + qris_masuk` |
| `created_at` | timestamp | | |
| `updated_at` | timestamp | | |
| `deleted_at` | timestamp | NULLABLE | Soft delete |

**Notes:**
- A shift is considered active when `jam_selesai IS NULL`
- Only one active shift per kasir at a time is enforced at application level
- `total_masuk` should be kept in sync via application logic or a DB trigger

---

### `kategori`

Menu categories used to group items on the Halaman Menu.

| Column | Type | Constraint | Description |
|--------|------|-----------|-------------|
| `id_kategori` | int(11) | PK | Primary key |
| `kategori` | varchar(50) | NOT NULL | Category name (e.g., Makanan, Minuman, Snack) |

**Notes:**
- No `created_at` / `updated_at` / `deleted_at` columns in the original diagram; consider adding for consistency
- Soft-deleting a kategori should hide all associated menu items from Halaman Menu

---

### `menu`

All food and beverage items offered by the restaurant.

| Column | Type | Constraint | Description |
|--------|------|-----------|-------------|
| `id_menu` | int(11) | PK | Primary key |
| `nama_menu` | varchar(255) | NOT NULL | Item name |
| `id_kategori` | int(11) | FK → `kategori.id_kategori` | Category |
| `foto` | varchar(255) | NULLABLE | Image file path |
| `harga` | int(11) | NOT NULL | Price in IDR |
| `status` | enum('habis', 'tersedia') | NOT NULL | Availability status |
| `created_at` | timestamp | | |
| `updated_at` | timestamp | | |
| `deleted_at` | timestamp | NULLABLE | Soft delete |

**Notes:**
- Items with `status = 'habis'` are shown greyed out on Halaman Menu and cannot be added to cart
- Soft-deleted items are hidden from Halaman Menu and from Kelola Menu active list
- `harga` at the time of ordering is snapshotted into `detail_pesanan.harga_satuan` so price changes don't affect past orders

---

### `meja`

Restaurant tables. Each table has a unique QR code token.

| Column | Type | Constraint | Description |
|--------|------|-----------|-------------|
| `id_meja` | int(11) | PK | Primary key |
| `nomor_meja` | int(11) | NOT NULL | Display table number (e.g., 1, 2, 3) |
| `qrcode_token` | varchar(255) | NOT NULL, UNIQUE | Unique token embedded in the QR code URL |
| `status` | enum('kosong', 'terisi') | NOT NULL, DEFAULT 'kosong' | Current occupancy status |
| `created_at` | timestamp | | |
| `updated_at` | timestamp | | |
| `deleted_at` | timestamp | NULLABLE | Soft delete |

**Notes:**
- `qrcode_token` is auto-generated (UUID) when a new meja is created
- The QR code URL format: `/menu/{qrcode_token}`
- Token can be regenerated from Kelola Meja, which invalidates the old QR
- `status` is set to `terisi` when an order is submitted, and back to `kosong` after payment is completed
- Soft-deleted tables should not appear in the QR menu flow

---

### `pesanan`

Each pesanan represents one complete order session for a table.

| Column | Type | Constraint | Description |
|--------|------|-----------|-------------|
| `id_pesanan` | int(11) | PK | Primary key |
| `kode_struk` | varchar(255) | NOT NULL, UNIQUE | Receipt/invoice code (e.g., `STR-20250527-001`) |
| `id_meja` | int(11) | FK → `meja.id_meja` | Table that placed the order |
| `metode_pembayaran` | enum('cash', 'qris') | NOT NULL | Payment method |
| `total_harga` | int(11) | NOT NULL | Sum of all item subtotals |
| `pajak` | int(11) | NOT NULL | Tax amount (calculated from Web Setting tax rate) |
| `total_bayar` | int(11) | NOT NULL | `total_harga + pajak` |
| `id_user` | int(11) | FK → `users.id_user` | Kasir who processed the payment |
| `created_at` | timestamp | | Order creation time |
| `deleted_at` | timestamp | NULLABLE | Soft delete |

**Notes:**
- `pesanan` is created when the Kasir confirms payment, not when the customer submits the order
- Customer submissions create `detail_pesanan` records in a pending/draft state first
- `kode_struk` is generated at payment time in a human-readable format

---

### `detail_pesanan`

Each row is one menu item line within an order.

| Column | Type | Constraint | Description |
|--------|------|-----------|-------------|
| `id_detail` | int(11) | PK | Primary key |
| `id_pesanan` | int(11) | FK → `pesanan.id_pesanan` | Parent order |
| `id_menu` | int(11) | FK → `menu.id_menu` | Ordered menu item |
| `jumlah` | int(11) | NOT NULL | Quantity ordered |
| `harga_satuan` | int(11) | NOT NULL | Unit price **at time of order** (snapshot) |
| `subtotal` | int(11) | NOT NULL | `jumlah × harga_satuan` |
| `catatan` | varchar(255) | NULLABLE | Special request or note from customer |
| `status` | enum('menunggu', 'dimasak', 'selesai') | NOT NULL, DEFAULT 'menunggu' | Kitchen cooking status |
| `created_at` | timestamp | | |
| `updated_at` | timestamp | | |
| `deleted_at` | timestamp | NULLABLE | Soft delete |

**Notes:**
- `harga_satuan` is a snapshot of `menu.harga` at the time of order — not a live reference
- `status` is updated by Chef via Daftar Pesanan
- Soft-deleting a detail line does not affect the parent `pesanan` totals (handled at application level)

---

### `activity_log`

Audit log for all significant user actions in the system.

| Column | Type | Constraint | Description |
|--------|------|-----------|-------------|
| `id_log` | int(11) | PK | Primary key |
| `id_user` | int(11) | FK → `users.id_user` | User who performed the action |
| `aktivitas` | varchar(255) | NOT NULL | Short action label (e.g., `LOGIN`, `CREATE_MENU`, `DELETE_USER`) |
| `detail_aktivitas` | varchar(255) | NULLABLE | Longer description or affected data |
| `ip_address` | varchar(255) | NULLABLE | Client IP address |
| `created_at` | timestamp | | When the action occurred |
| `deleted_at` | timestamp | NULLABLE | Soft delete (Superadmin can archive logs) |

**Notes:**
- Written automatically via application-level middleware or model observers
- Logged events: login, logout, create, update, soft delete, restore, permanent delete, backup, settings change
- Read-only from the UI (Log Aktivitas page)

---

### `history_update`

Tracks before/after data for every record update. Enables field-level change auditing.

| Column | Type | Constraint | Description |
|--------|------|-----------|-------------|
| `id_update` | int(11) | PK | Primary key |
| `table` | int(11) | FK (logical) | Name of the affected table |
| `record_id` | int(11) | NOT NULL | ID of the modified record |
| `data_lama` | varchar(255) | NULLABLE | JSON snapshot of data before the change |
| `data_baru` | varchar(255) | NULLABLE | JSON snapshot of data after the change |
| `created_at` | timestamp | | When the change was made |
| `updated_at` | timestamp | | |
| `deleted_at` | timestamp | NULLABLE | Soft delete |

**Notes:**
- Written by Eloquent model observers on the `updating` event
- `data_lama` and `data_baru` store JSON strings for easy comparison
- Covers all tables that have an Edit function: `users`, `menu`, `kategori`, `meja`, `shift`, `pesanan`, `detail_pesanan`, `aksess`

---

## 3. Relationships Summary

| Relationship | Type | FK Column |
|-------------|------|-----------|
| `role` → `users` | One-to-Many | `users.id_role` |
| `role` → `aksess` | One-to-Many | `aksess.id_role` |
| `users` → `shift` | One-to-Many | `shift.id_user` |
| `users` → `pesanan` | One-to-Many | `pesanan.id_user` |
| `users` → `activity_log` | One-to-Many | `activity_log.id_user` |
| `meja` → `pesanan` | One-to-Many | `pesanan.id_meja` |
| `pesanan` → `detail_pesanan` | One-to-Many | `detail_pesanan.id_pesanan` |
| `menu` → `detail_pesanan` | One-to-Many | `detail_pesanan.id_menu` |
| `kategori` → `menu` | One-to-Many | `menu.id_kategori` |

---

## 4. Soft Delete Policy

All major tables include a `deleted_at` timestamp column. The following rules apply:

| Action | Behavior |
|--------|----------|
| **Soft Delete** | Sets `deleted_at = NOW()`. Record is hidden from all active queries. |
| **Restore** | Sets `deleted_at = NULL`. Record returns to active list. |
| **Permanent Delete** | Executes hard `DELETE`. Irreversible. Available from Trash view only. |
| **Default Query Scope** | Always filters `WHERE deleted_at IS NULL` unless explicitly viewing Trash. |

Tables with soft delete: `users`, `aksess`, `shift`, `menu`, `meja`, `pesanan`, `detail_pesanan`, `activity_log`, `history_update`

Tables without soft delete: `role`, `kategori` *(consider adding for consistency)*

---

## 5. Key Enums

| Table | Column | Values | Description |
|-------|--------|--------|-------------|
| `menu` | `status` | `tersedia`, `habis` | Whether item is available to order |
| `meja` | `status` | `kosong`, `terisi` | Whether table is currently occupied |
| `pesanan` | `metode_pembayaran` | `cash`, `qris` | Payment method used |
| `detail_pesanan` | `status` | `menunggu`, `dimasak`, `selesai` | Kitchen cooking progress |
| `aksess` | `allowed` | `0`, `1` | `1` = access granted, `0` = denied |
