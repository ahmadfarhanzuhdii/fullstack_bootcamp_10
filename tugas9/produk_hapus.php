<?php
declare(strict_types=1);

require_once __DIR__ . '/helpers.php';

requirePost();   // hapus data tidak boleh lewat GET (link biasa)
csrf_verify();

$id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

if ($id === false) {
    flash('danger', 'ID produk tidak valid.');
    redirect('index.php');
}

try {
    $pdo  = getConnection();
    $stmt = $pdo->prepare('DELETE FROM produk WHERE id = :id');
    $stmt->execute([':id' => $id]);

    if ($stmt->rowCount() > 0) {
        // Bersihkan juga dari keranjang sesi ini jika ada
        unset($_SESSION['cart'][$id]);
        flash('success', 'Produk berhasil dihapus.');
    } else {
        flash('danger', 'Produk tidak ditemukan atau sudah dihapus.');
    }
} catch (PDOException $e) {
    error_log($e->getMessage());
    flash('danger', 'Terjadi kesalahan saat menghapus produk.');
}

redirect('index.php');
