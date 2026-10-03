CREATE DATABASE IF NOT EXISTS toko_db
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE toko_db;

CREATE TABLE IF NOT EXISTS produk (
    id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nama       VARCHAR(100)  NOT NULL,
    harga      DECIMAL(12,2) NOT NULL,
    deskripsi  TEXT          NOT NULL,
    created_at TIMESTAMP     DEFAULT CURRENT_TIMESTAMP
);
