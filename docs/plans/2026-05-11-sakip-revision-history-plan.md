# SAKIP Revision History Implementation Plan

> **For Claude:** REQUIRED SUB-SKILL: Use superpowers:executing-plans to implement this plan task-by-task.

**Goal:** Menambahkan history revisi dokumen SAKIP dengan checkbox revisi di admin-panel dan modal history di Joomla.

**Architecture:** Backend Lumen menyimpan revisi pada tabel `sakip_revisions` dan mengirim relasi revisi pada API publik. Admin-panel memakai checkbox untuk memilih update dokumen awal atau membuat revisi baru. Joomla memakai data revisi dari API untuk menampilkan versi aktif dan modal history.

**Tech Stack:** Lumen 11, Eloquent, MySQL migrations, Next.js App Router, TypeScript, Radix/shadcn dialog, Joomla HTML/JavaScript.

---

### Task 1: Pengaman Penomoran Revisi

**Files:**
- Create: `tests/Unit/SakipRevisionSequenceTest.php`
- Create: `app/Support/SakipRevisionSequence.php`

**Steps:**
1. Tulis test untuk nomor revisi berikutnya dari nilai maksimum saat ini.
2. Jalankan PHPUnit dan pastikan test gagal karena class belum ada.
3. Implementasikan helper kecil `SakipRevisionSequence::next`.
4. Jalankan PHPUnit dan pastikan test lulus.

### Task 2: Backend Data Model dan API

**Files:**
- Create: `database/migrations/2026_05_11_000001_add_publish_date_to_sakip_table.php`
- Create: `database/migrations/2026_05_11_000002_create_sakip_revisions_table.php`
- Create: `app/Models/SakipRevision.php`
- Modify: `app/Models/Sakip.php`
- Modify: `app/Http/Controllers/SakipController.php`
- Modify: `routes/web.php` bila endpoint tambahan diperlukan

**Steps:**
1. Tambahkan migration publish date dan tabel revision.
2. Tambahkan relasi Eloquent.
3. Update response API agar menyertakan `revisions`, `latest_revision`, dan `dokumen_aktif`.
4. Update `update` agar `is_revisi=1` membuat revision baru setelah validasi dokumen awal dan dokumen revisi.

### Task 3: Admin Panel

**Files:**
- Modify: `E:/project/admin-panel/lib/api.ts`
- Modify: `E:/project/admin-panel/app/sakip/page.tsx`
- Modify: `E:/project/admin-panel/app/sakip/tambah/page.tsx`
- Modify: `E:/project/admin-panel/app/sakip/[id]/edit/page.tsx`

**Steps:**
1. Tambahkan type revision dan field publish date.
2. Tambahkan input link/tanggal publish pada tambah dan edit.
3. Tambahkan checkbox revisi pada edit dengan validasi dokumen awal.
4. Tambahkan tombol/modal history pada list dan perbaiki filter jenis.

### Task 4: Joomla Integration

**Files:**
- Modify: `docs/joomla-integration-sakip.html`

**Steps:**
1. Render versi aktif dari `latest_revision` jika ada.
2. Tambahkan tombol history.
3. Tambahkan modal history berisi revisi, tanggal publish, keterangan, dan link dokumen.

### Task 5: Verifikasi

**Commands:**
- `vendor/bin/phpunit tests/Unit/SakipRevisionSequenceTest.php --bootstrap vendor/autoload.php`
- `composer validate --no-check-publish`
- PHP lint untuk `app`, `bootstrap`, `config`, `routes`, `database`
- `npm run build`
- `npm run lint`
