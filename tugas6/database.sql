CREATE DATABASE IF NOT EXISTS ecommerce_db;
USE ecommerce_db;

CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama_produk VARCHAR(100) NOT NULL,
    harga DECIMAL(15,2) NOT NULL,
    deskripsi TEXT,
    stok INT NOT NULL DEFAULT 0,
    kategori VARCHAR(50) NOT NULL DEFAULT 'Umum',
    gambar VARCHAR(500) DEFAULT NULL
);

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL
);

CREATE TABLE IF NOT EXISTS orders (
    order_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL,
    total DECIMAL(15,2) NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id)
        ON DELETE CASCADE ON UPDATE CASCADE
);

-- Tambahkan kolom bila database lama sudah memiliki tabel products.
-- Jalankan hanya jika kolom kategori/gambar belum ada:
-- ALTER TABLE products ADD COLUMN kategori VARCHAR(50) NOT NULL DEFAULT 'Umum';
-- ALTER TABLE products ADD COLUMN gambar VARCHAR(500) DEFAULT NULL;

INSERT INTO users (nama, email, password)
SELECT 'Demo User', 'demo@farhanstore.local', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC3dG7F0qYk3WmJYJQO'
WHERE NOT EXISTS (SELECT 1 FROM users WHERE email = 'demo@farhanstore.local');

INSERT INTO products (nama_produk, harga, deskripsi, stok, kategori, gambar)
SELECT * FROM (
    SELECT 'Laptop ASUS VivoBook', 8500000, 'Laptop ASUS VivoBook dengan performa tinggi untuk kebutuhan kerja dan multimedia.', 10, 'Laptop', 'https://images.unsplash.com/photo-1496181133206-80ce9b88a853'
    UNION ALL
    SELECT 'iPhone 15', 12500000, 'Smartphone premium dengan kamera berkualitas dan performa tinggi.', 10, 'Smartphone', 'https://images.unsplash.com/photo-1592899677977-9c10ca588bbd'
    UNION ALL
    SELECT 'Samsung Galaxy', 6500000, 'Smartphone Samsung dengan layar AMOLED dan baterai tahan lama.', 10, 'Smartphone', 'https://images.unsplash.com/photo-1610945415295-d9bbf067e59c'
    UNION ALL
    SELECT 'Gaming Headset', 750000, 'Headset gaming dengan microphone dan kualitas suara surround.', 20, 'Gaming', 'https://images.unsplash.com/photo-1599669454699-248893623440'
    UNION ALL
    SELECT 'Mechanical Keyboard', 950000, 'Keyboard mechanical RGB dengan switch responsif untuk gaming.', 20, 'Gaming', 'https://images.unsplash.com/photo-1587829741301-dc798b83add3'
    UNION ALL
    SELECT 'Wireless Mouse', 350000, 'Mouse wireless ergonomis dengan koneksi stabil.', 30, 'Accessories', 'https://images.unsplash.com/photo-1527814050087-3793815479db'
    UNION ALL
    SELECT 'Smart Watch', 1200000, 'Smart watch dengan fitur monitoring aktivitas dan notifikasi.', 15, 'Wearable', 'https://images.unsplash.com/photo-1523275335684-37898b6baf30'
    UNION ALL
    SELECT 'Bluetooth Speaker', 600000, 'Speaker Bluetooth portable dengan suara jernih dan bass kuat.', 15, 'Audio', 'https://images.unsplash.com/photo-1608043152269-423dbba4e7e1'
) AS seed
WHERE NOT EXISTS (SELECT 1 FROM products LIMIT 1);
