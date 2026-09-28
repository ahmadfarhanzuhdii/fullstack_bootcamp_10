<?php
require_once __DIR__ . '/config.php';

try {
    $stmt = $pdo->query("
        SELECT
            id,
            nama_produk AS nama,
            harga,
            deskripsi,
            stok,
            kategori,
            gambar
        FROM products
        ORDER BY id ASC
    ");

    echo json_encode([
        'success' => true,
        'data' => $stmt->fetchAll()
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Gagal mengambil data produk.'
    ]);
}
