# Panduan Penggunaan Web (Project UTS)

Panduan ini menjelaskan cara menjalankan project web dari GitHub di komputer lokal menggunakan **XAMPP** dan **Visual Studio Code**.

## Persiapan

Pastikan sudah terpasang:

- XAMPP (Apache dan MySQL)
- Visual Studio Code
- Ekstensi SQL/MySQL di VS Code (contoh: _MySQL_ oleh Weijan Chen, atau _SQLTools_)

---

## 1. Download File dari GitHub

1. Buka halaman repository GitHub project.
2. Klik tombol hijau **Code**.
3. Pilih **Download ZIP**.
4. Tunggu sampai file ZIP selesai diunduh.

## 2. Ekstrak File

1. Cari file ZIP hasil download (biasanya di folder _Downloads_).
2. Klik kanan, lalu pilih **Extract All...** (atau _Extract Here_).
3. Hasilnya berupa sebuah folder yang berisi file web dan file `.sql`.

## 3. Buka Folder di Visual Studio Code

1. Buka **Visual Studio Code**.
2. Klik **File > Open Folder...**
3. Pilih folder hasil ekstrak tadi.
4. Pastikan terlihat file web (PHP/HTML/dll) dan file **`.sql`** di panel Explorer.

## 4. Jalankan MySQL di XAMPP

Database harus aktif lebih dulu sebelum dibuat.

1. Buka **XAMPP Control Panel**.
2. Klik **Start** pada **MySQL**.
3. Klik **Start** pada **Apache**.
4. Pastikan keduanya berwarna hijau.

## 5. Buat Database `uts`

Buat database baru bernama **`uts`** (huruf kecil semua). Jalankan query berikut di VS Code melalui koneksi MySQL:

```sql
CREATE DATABASE uts;
USE uts;
```

> Koneksi MySQL XAMPP bawaan: host `localhost`, port `3306`, user `root`, password kosong.

## 6. Masukkan Query dari File SQL

1. Buka file **`.sql`** di VS Code.
2. Baca isi file dan perhatikan **urutan query**-nya.
3. Jalankan query **satu per satu sesuai urutan**, dari paling atas ke bawah.
4. Urutan yang umum:
   1. Query `CREATE TABLE` (membuat tabel)
   2. Query `INSERT INTO` (mengisi data, termasuk akun login)
5. Pastikan tidak ada error di setiap langkah sebelum lanjut ke query berikutnya.

> **Penting:** Jangan melewati atau membalik urutan query. Tabel harus dibuat dulu sebelum data dimasukkan.

## 7. Pindahkan File Web ke `htdocs`

1. Salin folder web hasil ekstrak.
2. Buka folder instalasi XAMPP, biasanya:

   ```
   C:\xampp\htdocs
   ```

3. Tempel (_paste_) folder web ke dalam `htdocs`.
4. Contoh hasil akhir:

   ```
   C:\xampp\htdocs\nama-folder-web
   ```

## 8. Jalankan Web di Browser

1. Pastikan **Apache** dan **MySQL** masih berstatus **Running** di XAMPP.
2. Buka browser (Chrome/Edge/Firefox).
3. Ketik alamat berikut:

   ```
   http://localhost/nama-folder-web
   ```

   Ganti `nama-folder-web` dengan nama folder yang ada di `htdocs`.

## 9. Login ke Web

Masukkan username dan password yang terdaftar di data SQL (query `INSERT` pada tabel user/admin):

Setelah berhasil login, web siap digunakan.

## Ringkasan Alur

1. Download ZIP dari GitHub
2. Ekstrak
3. Buka folder di VS Code
4. Start MySQL dan Apache di XAMPP
5. Buat database `uts`
6. Jalankan query SQL satu per satu sesuai urutan
7. Salin folder web ke `htdocs`
8. Buka `http://localhost/nama-folder-web`
9. Login dengan username dan password
