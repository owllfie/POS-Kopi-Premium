# 🔄 Workflow — Restaurant POS System

> Describes all key system flows: authentication, QR ordering, order processing, and role interactions.
> Based on the Context Diagram, Activity Diagram, and Swimlane Diagram.

---

## Table of Contents

1. [System Actors](#1-system-actors)
2. [Authentication Flow](#2-authentication-flow)
3. [Customer Order Flow (QR-based)](#3-customer-order-flow-qr-based)
4. [Order Processing Flow (Kasir & Chef)](#4-order-processing-flow-kasir--chef)
5. [Payment Flow](#5-payment-flow)
6. [Reporting Flow](#6-reporting-flow)
7. [Shift Flow (Kasir)](#7-shift-flow-kasir)
8. [Admin / Superadmin Management Flow](#8-admin--superadmin-management-flow)
9. [Soft Delete & Restore Flow](#9-soft-delete--restore-flow)
10. [Context Diagram — Role Interactions](#10-context-diagram--role-interactions)

---

## 1. System Actors

| Actor | Type | Entry Point |
|-------|------|-------------|
| **Customer** | External / Guest | QR Code scan → `/menu/{token}` |
| **Kasir** | Internal | Login → Dashboard Kasir |
| **Chef** | Internal | Login → Daftar Pesanan |
| **Manager** | Internal | Login → Dashboard Manager |
| **Admin** | Internal | Login → Dashboard Admin |
| **Superadmin** | Internal | Login → Dashboard Admin (extended) |

---

## 2. Authentication Flow

```
┌─────────────────────────────────────────────────────────┐
│                     /login page                         │
└─────────────────────────────────────────────────────────┘
                          │
                          ▼
                 Enter email + password
                          │
              ┌───────────┴───────────┐
              ▼                       ▼
         ❌ Invalid               ✅ Valid
              │                       │
     Show error message        Check id_role
     Stay on /login                   │
                         ┌────────────┼────────────┬────────────┬──────────────┐
                         ▼            ▼            ▼            ▼              ▼
                      Kasir         Chef        Manager       Admin        Superadmin
                         │            │            │            │              │
                         ▼            ▼            ▼            ▼              ▼
                   Dashboard      Daftar       Dashboard    Dashboard      Dashboard
                    Kasir         Pesanan      Manager       Admin          Admin
```

**Rules:**
- Already-authenticated users visiting `/login` are redirected to their dashboard
- Guest (Customer) users are never routed through `/login`
- Failed login attempts are logged after 3 consecutive failures (optional: account lockout)
- On logout, session is destroyed and user is redirected to `/login`

---

## 3. Customer Order Flow (QR-based)

This is the primary ordering flow triggered by a customer scanning a QR code at their table.

```
┌──────────────┐   ┌──────────────────┐   ┌──────────────┐   ┌──────────────┐
│   Customer   │   │      System      │   │    Kasir     │   │     Chef     │
└──────┬───────┘   └────────┬─────────┘   └──────┬───────┘   └──────┬───────┘
       │                    │                     │                   │
       ▼                    │                     │                   │
 Datang ke restoran         │                     │                   │
       │                    │                     │                   │
       ▼                    │                     │                   │
   Cari meja                │                     │                   │
       │                    │                     │                   │
       ▼                    │                     │                   │
 Scan QR di meja ──────────►│                     │                   │
                            │                     │                   │
                   Look up qrcode_token            │                   │
                   in meja table                   │                   │
                            │                     │                   │
                   ┌────────┴────────┐             │                   │
                   ▼                 ▼             │                   │
               ❌ Not found     ✅ Found           │                   │
               Show error      Store id_meja       │                   │
                               in session          │                   │
                            │                     │                   │
                   Redirect to Halaman Menu        │                   │
                            │                     │                   │
       ◄────────────────────│                     │                   │
       │                    │                     │                   │
       ▼                    │                     │                   │
 Pilih menu yang            │                     │                   │
 ingin dipesan              │                     │                   │
       │                    │                     │                   │
       ▼                    │                     │                   │
 Submit pesanan ───────────►│                     │                   │
                            │                     │                   │
                   Save detail_pesanan             │                   │
                   with id_meja (auto)             │                   │
                   Set meja.status = 'terisi'      │                   │
                            │                     │                   │
                   Send order notification ───────►│                   │
                            │             Notifikasi pesanan masuk     │
                            │                     │                   │
                   ◄─────── Order confirmation     │                   │
       ◄────────────────────│                     │                   │
 Tampilkan konfirmasi        │                     │                   │
 pesanan                    │                     │                   │
```

**Key rules:**
- The `id_meja` is never entered manually by the customer — it is always derived from the QR token
- If the same table scans again while `status = 'terisi'`, they can add more items to the existing session (or a new order is created — define at implementation)
- Customer cannot access any page other than `/menu/{token}`

---

## 4. Order Processing Flow (Kasir & Chef)

After the customer submits, the order moves through the kitchen and back to the cashier.

```
         [New Order Created]
                │
                ▼
    detail_pesanan.status = 'menunggu'
                │
    ┌───────────┴────────────────────────┐
    ▼                                    ▼
[Kasir - Daftar Pesanan]         [Chef - Daftar Pesanan]
 Sees table number & items         Sees items grouped by table
 Waits for chef to cook            │
                                   ▼
                             Klik "Mulai Masak"
                                   │
                          status = 'dimasak'
                                   │
                                   ▼
                             Klik "Selesai"
                                   │
                          status = 'selesai'
                                   │
    ◄──────────────────────────────┘
    Order marked ready
    Kasir proceeds to payment
```

**Chef status transitions:**

```
menunggu  ──► dimasak  ──► selesai
```

- Chef can only move status forward (not backward)
- All items in a `pesanan` must be `selesai` before Kasir can process full payment (optional enforcement)
- Kasir can view partial status (some items still cooking) on Daftar Pesanan

---

## 5. Payment Flow

Triggered by Kasir when the customer is ready to pay.

```
[Kasir opens Halaman Pembayaran for a specific order]
                │
                ▼
     Show order summary:
     - Items, qty, subtotals
     - Pajak (from Web Setting)
     - Total Bayar
                │
                ▼
     Kasir selects payment method
                │
     ┌──────────┴──────────┐
     ▼                     ▼
   CASH                  QRIS
     │                     │
  Enter nominal         Show QR code
  received              for customer
     │                     │
  Calculate             Confirm payment
  kembalian             received
     │                     │
     └──────────┬──────────┘
                ▼
     Confirm & Save pesanan record
                │
                ▼
     Generate kode_struk
                │
                ▼
     Print / display struk (receipt)
                │
                ▼
     meja.status = 'kosong'
                │
                ▼
     shift.cash_masuk / qris_masuk updated
```

**Rules:**
- A `pesanan` record is only created upon confirmed payment — not before
- `kode_struk` format: `STR-YYYYMMDD-XXX` (e.g., `STR-20250527-003`)
- If payment is cancelled mid-flow, no `pesanan` is saved and the order remains pending
- QRIS transactions auto-confirm via integration or manual confirmation by Kasir

---

## 6. Reporting Flow

### Manager / Admin / Superadmin

```
User opens Laporan page
        │
        ▼
Select report type:
  ┌─────┬──────────┬──────────┐
  ▼     ▼          ▼          ▼
Harian  Mingguan  Bulanan  Transaksi
  │     │          │          │
  └─────┴──────────┴──────────┘
        │
        ▼
Apply filters (date range, kasir, metode)
        │
        ▼
System queries pesanan + detail_pesanan
        │
        ▼
Render chart + summary table
        │
        ▼
Optional: Export PDF / CSV
```

### Kasir (Laporan Harian — via Dashboard)

```
Kasir opens Dashboard Kasir
        │
        ▼
System loads today's shift data:
- Pesanan linked to id_user = current kasir
- Date = today
        │
        ▼
Show:
  - Total transaksi
  - Total pendapatan (cash + QRIS)
  - Cash di tangan vs cash transaksi
  - Selisih (discrepancy highlight)
```

---

## 7. Shift Flow (Kasir)

```
Kasir logs in
      │
      ▼
Check: is there an active shift for this user?
      │
 ┌────┴────┐
 ▼         ▼
Yes        No
 │         │
Stay     Prompt to start shift
active   Enter kas awal (opening cash)
              │
              ▼
         shift.jam_mulai = NOW()
         shift record created
              │
    ─────── Work ───────
              │
              ▼
        Kasir ends shift
              │
              ▼
         Enter kas di tangan
         (physical cash count)
              │
              ▼
     shift.jam_selesai = NOW()
     Compare: kas di tangan vs cash_masuk
              │
     ┌────────┴────────┐
     ▼                 ▼
  Selisih = 0       Selisih ≠ 0
  ✅ Selesai        ⚠️ Warning shown
                    Kasir confirms anyway
                            │
                            ▼
                    Shift closed with note
```

---

## 8. Admin / Superadmin Management Flow

General CRUD pattern applied to all Kelola pages (Menu, Kategori, Meja, Users, Shift):

```
Open Kelola [Entity] page
        │
        ▼
View active records list (deleted_at IS NULL)
        │
   ┌────┴──────────────────────────┐
   ▼                               ▼
[Create]                    [Select existing record]
   │                               │
   ▼                          ┌────┴────┐
Fill form                     ▼         ▼
   │                        [Edit]   [Delete]
   ▼                          │         │
Validate                   Edit form  Soft delete
   │                          │       (deleted_at = NOW())
   ▼                       Validate    │
Save → history_update          │       ▼
written                     Save    Record hidden
   │                          │     from active list
   ▼                   history_update
Active list             written
refreshed
```

**Trash flow (visible in Trash tab):**

```
Open Trash tab (deleted_at IS NOT NULL)
        │
   ┌────┴────┐
   ▼         ▼
[Restore]  [Permanent Delete]
   │              │
deleted_at=NULL  Hard DELETE
   │              │
Back to         Irreversible ⚠️
active list
```

**Kelola Meja — QR generation sub-flow:**

```
Admin creates new meja
        │
        ▼
System auto-generates UUID as qrcode_token
        │
        ▼
QR code image rendered from: /menu/{qrcode_token}
        │
        ▼
Admin downloads / prints QR for the table

(Optional: Admin clicks "Regenerate QR")
        │
        ▼
New UUID generated → old QR invalidated
```

---

## 9. Soft Delete & Restore Flow

Applied system-wide to all manageable entities.

```
Active Record
     │
     ▼ (click Delete)
Soft Deleted (deleted_at = NOW())
     │
     ├──► Hidden from all active page views
     ├──► Cannot be used in new orders/relations
     └──► Visible in Trash tab only
              │
         ┌────┴────┐
         ▼         ▼
     [Restore]  [Permanent Delete]
         │              │
    deleted_at=NULL   Hard SQL DELETE
         │              │
    Returns to       Cannot be undone
    active list      No record remains
```

**history_update tracking on edit:**

```
User submits edit form
        │
        ▼
Eloquent model observer fires (updating event)
        │
        ▼
Capture: old values (before save) + new values (after)
        │
        ▼
Write to history_update:
  - table name
  - record_id
  - data_lama (JSON)
  - data_baru (JSON)
  - created_at = NOW()
        │
        ▼
Original record saved with updated_at = NOW()
```

---

## 10. Context Diagram — Role Interactions

Based on the Context Diagram, each actor interacts with the Sistem POS as follows:

```
                          ┌─────────────┐
                          │   Manager   │
                          └──────┬──────┘
               Melihat laporan   │   Menampilkan laporan
          ◄────────────────────  │  ────────────────────►
                                 │
  ┌───────┐  Mengelola data      │       Menerima notifikasi   ┌───────┐
  │ Admin │  master & laporan    │       pesanan               │ Kasir │
  └───┬───┘                      ▼                             └───┬───┘
      │              ┌───────────────────┐                         │
      │─────────────►│    Sistem POS     │◄────────────────────────│
      │◄─────────────│                   │─────────────────────────►
      │  Memberi     └───────────────────┘  Memproses transaksi    │
      │  akses data        ▲    │           Mencetak struk          │
      │  master &          │    │
      │  laporan           │    ▼
                   Melihat antrean     Menampilkan antrean pesanan
                   pesanan        │
                          ┌──────┴──────┐
                          │    Chef     │
                          └─────────────┘
```

### Interaction Summary

| Actor | Sends to System | Receives from System |
|-------|----------------|---------------------|
| **Manager** | Request laporan (harian/mingguan/bulanan) | Laporan data & charts |
| **Admin** | Mengelola data master (menu, meja, users, dll) + request laporan | Data master updated, laporan, akses dikonfirmasi |
| **Kasir** | Konfirmasi pesanan, proses pembayaran, cetak struk | Notifikasi pesanan masuk, data transaksi |
| **Chef** | Update status masakan (dimasak / selesai) | Antrean pesanan baru |
| **Customer** | QR scan, pilih menu, submit pesanan | Redirect halaman menu, konfirmasi pesanan |
