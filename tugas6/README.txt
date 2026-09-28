# Farhan Store - PHP + MySQL

## Struktur
farhan_store_mysql/
├── index.html
├── database.sql
└── api/
    ├── config.php
    ├── products.php
    └── orders.php

## Cara menjalankan dengan XAMPP
1. Start Apache dan MySQL.
2. Copy folder `farhan_store_mysql` ke `C:/xampp/htdocs/`.
3. Buka phpMyAdmin.
4. Import `database.sql`.
5. Jika MySQL Anda memakai password, ubah `$pass` di `api/config.php`.
6. Buka:
   http://localhost/farhan_store_mysql/

## Koneksi
Browser tidak langsung terhubung ke MySQL. Alurnya:
HTML/JavaScript -> PHP API -> MySQL.

## Catatan
- `products.php` mengambil produk dari database.
- `orders.php` menyimpan checkout ke tabel orders dan mengurangi stok.
- Checkout demo memakai `user_id = 1`, yang dibuat oleh database.sql.
- Untuk produksi, user_id seharusnya berasal dari sistem login/session, bukan hard-code.
