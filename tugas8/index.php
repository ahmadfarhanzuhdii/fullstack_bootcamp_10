<?php
declare(strict_types=1);

require_once __DIR__ . '/koneksi.php';

function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function rupiah(float|string $angka): string
{
    return 'Rp ' . number_format((float) $angka, 0, ',', '.');
}

$pdo = getConnection();

/* ---------------------------------------------------------
 * 1. Ambil & validasi parameter filter dari URL (?kategori=2)
 *    filter_input memastikan nilainya integer positif,
 *    selain itu dianggap "semua kategori".
 * --------------------------------------------------------- */
$kategoriId = filter_input(
    INPUT_GET,
    'kategori',
    FILTER_VALIDATE_INT,
    ['options' => ['min_range' => 1]]
);
$kategoriId = $kategoriId === false ? null : $kategoriId;

/* ---------------------------------------------------------
 * 2. Daftar kategori + jumlah produk (untuk menu filter)
 * --------------------------------------------------------- */
$daftarKategori = $pdo->query(
    'SELECT k.id, k.nama, COUNT(p.id) AS jumlah
       FROM kategori k
       LEFT JOIN produk p ON p.kategori_id = k.id
   GROUP BY k.id, k.nama
   ORDER BY k.nama'
)->fetchAll();

/* ---------------------------------------------------------
 * 3. Ambil produk. Query dibangun dinamis, tetapi nilai
 *    selalu lewat placeholder (aman dari SQL injection).
 * --------------------------------------------------------- */
$sql = 'SELECT p.id, p.nama, p.harga, p.deskripsi, k.nama AS kategori
          FROM produk p
          LEFT JOIN kategori k ON k.id = p.kategori_id';
$params = [];

if ($kategoriId !== null) {
    $sql .= ' WHERE p.kategori_id = :kategori_id';
    $params[':kategori_id'] = $kategoriId;
}

$sql .= ' ORDER BY p.created_at DESC, p.id DESC';

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$produk = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Toko Online</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: system-ui, sans-serif; background:#f4f5f7; margin:0; color:#1f2937; }
        header { background:#2563eb; color:#fff; padding:1rem 2rem; }
        header h1 { margin:0; font-size:1.4rem; }
        main { max-width:1100px; margin:auto; padding:1.5rem; }

        .filter { display:flex; flex-wrap:wrap; gap:.5rem; margin-bottom:1.5rem; }
        .filter a { padding:.45rem .9rem; border-radius:999px; background:#fff; color:#374151;
                    text-decoration:none; border:1px solid #d1d5db; font-size:.9rem; }
        .filter a:hover { border-color:#2563eb; }
        .filter a.aktif { background:#2563eb; color:#fff; border-color:#2563eb; }

        .grid { display:grid; grid-template-columns:repeat(auto-fill, minmax(230px, 1fr)); gap:1rem; }
        .card { background:#fff; border-radius:8px; padding:1.1rem; display:flex; flex-direction:column;
                box-shadow:0 1px 4px rgba(0,0,0,.08); }
        .badge { align-self:flex-start; font-size:.75rem; background:#e0e7ff; color:#3730a3;
                 padding:.15rem .6rem; border-radius:999px; margin-bottom:.6rem; }
        .card h2 { font-size:1.05rem; margin:0 0 .4rem; }
        .card p  { font-size:.9rem; color:#6b7280; flex:1; margin:0 0 .8rem; }
        .harga { font-weight:700; color:#2563eb; font-size:1.1rem; }
        .kosong { background:#fff; padding:2rem; text-align:center; border-radius:8px; color:#6b7280; }
    </style>
</head>
<body>
<header><h1>Toko Online</h1></header>

<main>
    <!-- MENU FILTER KATEGORI -->
    <nav class="filter" aria-label="Filter kategori">
        <a href="index.php" class="<?= $kategoriId === null ? 'aktif' : '' ?>">Semua</a>

        <?php foreach ($daftarKategori as $kat): ?>
            <a href="index.php?kategori=<?= (int) $kat['id'] ?>"
               class="<?= $kategoriId === (int) $kat['id'] ? 'aktif' : '' ?>">
                <?= e($kat['nama']) ?> (<?= (int) $kat['jumlah'] ?>)
            </a>
        <?php endforeach; ?>
    </nav>

    <!-- DAFTAR PRODUK (LOOPING) -->
    <?php if (empty($produk)): ?>
        <div class="kosong">Belum ada produk pada kategori ini.</div>
    <?php else: ?>
        <section class="grid">
            <?php foreach ($produk as $item): ?>
                <article class="card">
                    <span class="badge"><?= e($item['kategori'] ?? 'Tanpa kategori') ?></span>
                    <h2><?= e($item['nama']) ?></h2>
                    <p><?= e($item['deskripsi']) ?></p>
                    <div class="harga"><?= rupiah($item['harga']) ?></div>
                </article>
            <?php endforeach; ?>
        </section>
    <?php endif; ?>
</main>
</body>
</html>
