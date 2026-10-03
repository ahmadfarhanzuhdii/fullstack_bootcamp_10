<?php
declare(strict_types=1);

/* =========================================================
 * TUGAS 7 - DASAR PHP
 * Variabel diawali tanda $, operator, dan if-else
 * ========================================================= */

// Konfigurasi database (variabel)
$dbHost = 'localhost';
$dbName = 'toko_db';
$dbUser = 'root';
$dbPass = '';

// Variabel penampung state halaman
$errors  = [];     // array untuk menampung pesan error
$sukses  = false;  // boolean
$nama = $harga = $deskripsi = ''; // nilai awal form (agar tidak undefined)

/* =========================================================
 * Proses form hanya jika dikirim dengan method POST
 * ========================================================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {   // operator perbandingan ===

    // Ambil input & bersihkan spasi di awal/akhir (trim)
    $nama      = trim($_POST['nama']      ?? '');  // ?? = null coalescing
    $harga     = trim($_POST['harga']     ?? '');
    $deskripsi = trim($_POST['deskripsi'] ?? '');

    /* =====================================================
     * TUGAS 3 - VALIDASI
     * ===================================================== */

    // Nama: tidak boleh kosong & maksimal 100 karakter
    if ($nama === '') {
        $errors['nama'] = 'Nama produk wajib diisi.';
    } elseif (mb_strlen($nama) > 100) {
        $errors['nama'] = 'Nama produk maksimal 100 karakter.';
    }

    // Harga: tidak boleh kosong, harus angka, dan harus lebih dari 0
    if ($harga === '') {
        $errors['harga'] = 'Harga wajib diisi.';
    } elseif (!is_numeric($harga)) {              // operator logika !
        $errors['harga'] = 'Harga harus berupa angka.';
    } elseif ((float) $harga <= 0) {              // operator <= dan casting
        $errors['harga'] = 'Harga harus lebih besar dari 0.';
    }

    // Deskripsi: tidak boleh kosong
    if ($deskripsi === '') {
        $errors['deskripsi'] = 'Deskripsi wajib diisi.';
    }

    /* =====================================================
     * Simpan ke database HANYA jika tidak ada error
     * ===================================================== */
    if (empty($errors)) {
        try {
            $pdo = new PDO(
                "mysql:host=$dbHost;dbname=$dbName;charset=utf8mb4",
                $dbUser,
                $dbPass,
                [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                ]
            );

            // Prepared statement -> mencegah SQL Injection
            $stmt = $pdo->prepare(
                'INSERT INTO produk (nama, harga, deskripsi)
                 VALUES (:nama, :harga, :deskripsi)'
            );
            $stmt->execute([
                ':nama'      => $nama,
                ':harga'     => (float) $harga,
                ':deskripsi' => $deskripsi,
            ]);

            $sukses = true;
            $nama = $harga = $deskripsi = ''; // kosongkan form setelah sukses

        } catch (PDOException $e) {
            // Jangan tampilkan detail error ke user di production; cukup log
            error_log($e->getMessage());
            $errors['db'] = 'Terjadi kesalahan saat menyimpan data. Silakan coba lagi.';
        }
    }
}

// Helper kecil untuk escape output (mencegah XSS)
function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Tambah Produk</title>
    <style>
        body   { font-family: system-ui, sans-serif; background:#f4f5f7; margin:0; padding:2rem; }
        .card  { max-width:480px; margin:auto; background:#fff; padding:1.5rem 2rem;
                 border-radius:8px; box-shadow:0 2px 8px rgba(0,0,0,.08); }
        h1     { margin-top:0; font-size:1.4rem; }
        label  { display:block; margin:1rem 0 .3rem; font-weight:600; }
        input, textarea { width:100%; padding:.6rem; border:1px solid #ccc;
                          border-radius:4px; font:inherit; box-sizing:border-box; }
        input.invalid, textarea.invalid { border-color:#d9534f; }
        .error { color:#d9534f; font-size:.85rem; margin-top:.25rem; }
        .alert { padding:.75rem 1rem; border-radius:4px; margin-bottom:1rem; }
        .alert-success { background:#dff0d8; color:#2d6a2d; }
        .alert-danger  { background:#f8d7da; color:#842029; }
        button { margin-top:1.2rem; padding:.7rem 1.4rem; border:0; border-radius:4px;
                 background:#2563eb; color:#fff; font:inherit; cursor:pointer; }
        button:hover { background:#1d4ed8; }
    </style>
</head>
<body>
<div class="card">
    <h1>Tambah Produk Baru</h1>

    <?php if ($sukses): ?>
        <div class="alert alert-success">Produk berhasil disimpan!</div>
    <?php endif; ?>

    <?php if (isset($errors['db'])): ?>
        <div class="alert alert-danger"><?= e($errors['db']) ?></div>
    <?php endif; ?>

    <!-- TUGAS 2 - FORM INPUT -->
    <form method="post" action="" novalidate>

        <label for="nama">Nama Produk</label>
        <input type="text" id="nama" name="nama"
               value="<?= e($nama) ?>"
               class="<?= isset($errors['nama']) ? 'invalid' : '' ?>">
        <?php if (isset($errors['nama'])): ?>
            <div class="error"><?= e($errors['nama']) ?></div>
        <?php endif; ?>

        <label for="harga">Harga (Rp)</label>
        <input type="number" id="harga" name="harga" step="0.01" min="0"
               value="<?= e($harga) ?>"
               class="<?= isset($errors['harga']) ? 'invalid' : '' ?>">
        <?php if (isset($errors['harga'])): ?>
            <div class="error"><?= e($errors['harga']) ?></div>
        <?php endif; ?>

        <label for="deskripsi">Deskripsi</label>
        <textarea id="deskripsi" name="deskripsi" rows="4"
                  class="<?= isset($errors['deskripsi']) ? 'invalid' : '' ?>"><?= e($deskripsi) ?></textarea>
        <?php if (isset($errors['deskripsi'])): ?>
            <div class="error"><?= e($errors['deskripsi']) ?></div>
        <?php endif; ?>

        <button type="submit">Simpan Produk</button>
    </form>
</div>
</body>
</html>
