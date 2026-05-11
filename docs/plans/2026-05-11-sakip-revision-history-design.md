# SAKIP Revision History Design

## Tujuan

Modul SAKIP perlu menyimpan riwayat revisi dokumen. Dokumen awal tetap menjadi data utama SAKIP, sedangkan revisi disimpan sebagai history dengan nomor revisi otomatis, tanggal publish, keterangan, dan link dokumen.

## Alur Admin

Pada halaman edit SAKIP, admin mendapat checkbox `Ini revisi dokumen`. Jika checkbox tidak dicentang, form memperbarui dokumen awal seperti alur lama. Jika checkbox dicentang, sistem wajib memastikan dokumen awal sudah ada. Jika belum ada, admin mendapat peringatan bahwa dokumen awal harus disimpan terlebih dahulu.

Saat revisi dibuat, sistem mengambil nomor revisi terakhir untuk dokumen tersebut lalu menyimpan revisi berikutnya: `Revisi 1`, `Revisi 2`, `Revisi 3`, dan seterusnya. Link revisi bisa berasal dari URL manual atau upload file.

## Struktur Data

Tabel `sakip` tetap menyimpan data utama: tahun, jenis dokumen, uraian, link dokumen awal, dan tanggal publish dokumen awal. Tabel baru `sakip_revisions` menyimpan banyak revisi untuk satu dokumen SAKIP.

Field revisi: `sakip_id`, `revisi_ke`, `tanggal_publish`, `keterangan`, `link_dokumen`, timestamps. Relasi menggunakan `Sakip hasMany SakipRevision`, dan revisi ikut terhapus ketika dokumen utama dihapus.

## Integrasi Joomla

API publik tetap kompatibel karena field lama masih ada. API menambahkan `revisions`, `latest_revision`, dan `dokumen_aktif`. Script Joomla memakai `latest_revision` sebagai versi aktif jika tersedia, serta menyediakan tombol `History` yang membuka modal berisi daftar revisi dan link dokumennya.
