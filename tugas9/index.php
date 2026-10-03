<?php
declare(strict_types=1);

require_once __DIR__ . '/helpers.php';

$pdo = getConnection();

/* Filter kategori dari URL (?kategori=2), divalidasi sebagai integer positif */
$kategoriId = filter_input(INPUT_GET, 'kategori', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
$kategoriId = $kategoriId === false ? null : $kategoriId;

/* Menu kategori + jumlah produk */
$daftarKategori = $pdo->query(
    'SELECT k.id, k.nama, COUNT(p.id) AS jumlah
       FROM kategori k
       LEFT JOIN produk p ON p.kategori_id = k.id
   GROUP BY k.id, k.nama
   ORDER BY k.nama'
)->fetchAll();

/* READ: ambil produk (query dinamis, nilai tetap lewat placeholder) */
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

/* Tujuan redirect setelah tambah ke keranjang (mempertahankan filter) */
$kembali = 'index.php' . ($kategoriId !== null ? '?kategori=' . $kategoriId : '');

$judul = 'Produk';
require __DIR__ . '/_header.php';
?>

<div class="toolbar">
    <nav class="filter" aria-label="Filter kategori">
        <a href="index.php" class="<?= $kategoriId === null ? 'aktif' : '' ?>">Semua</a>
        <?php foreach ($daftarKategori as $kat): ?>
            <a href="index.php?kategori=<?= (int) $kat['id'] ?>"
               class="<?= $kategoriId === (int) $kat['id'] ? 'aktif' : '' ?>">
                <?= e($kat['nama']) ?> (<?= (int) $kat['jumlah'] ?>)
            </a>
        <?php endforeach; ?>
    </nav>

    <a href="produk_form.php" class="btn btn-primary">+ Tambah Produk</a>
</div>

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

                <div class="aksi">
                    <!-- CART: tambah ke keranjang (POST + CSRF) -->
                    <form method="post" action="keranjang.php" class="inline">
                        <?= csrf_field() ?>
                        <input type="hidden" name="action" value="tambah">
                        <input type="hidden" name="produk_id" value="<?= (int) $item['id'] ?>">
                        <input type="hidden" name="kembali" value="<?= e($kembali) ?>">
                        <button type="submit" class="btn btn-primary">+ Keranjang</button>
                    </form>

                    <!-- UPDATE -->
                    <a href="produk_form.php?id=<?= (int) $item['id'] ?>" class="btn btn-light">Edit</a>

                    <!-- DELETE (POST + CSRF + konfirmasi) -->
                    <form method="post" action="produk_hapus.php" class="inline"
                          onsubmit="return confirm('Hapus produk ini? Tindakan tidak bisa dibatalkan.');">
                        <?= csrf_field() ?>
                        <input type="hidden" name="id" value="<?= (int) $item['id'] ?>">
                        <button type="submit" class="btn btn-danger">Hapus</button>
                    </form>
                </div>
            </article>
        <?php endforeach; ?>
    </section>
<?php endif; ?>

<?php require __DIR__ . '/_footer.php'; ?>
