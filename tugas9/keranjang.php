<?php
declare(strict_types=1);

require_once __DIR__ . '/helpers.php';

const MAX_QTY = 99;

$pdo = getConnection();

/** Hanya izinkan redirect balik ke halaman produk milik kita sendiri. */
function safeBack(string $url): string
{
    return preg_match('/^index\.php(\?kategori=\d+)?$/', $url) ? $url : 'index.php';
}

/* Struktur keranjang di session: [produk_id => qty]
 * Harga TIDAK disimpan di session; selalu diambil dari database
 * agar tidak bisa dimanipulasi dari sisi client. */
$_SESSION['cart'] ??= [];

/* =========================================================
 * Aksi keranjang (semua lewat POST + CSRF, lalu redirect)
 * ========================================================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();

    $action = (string) ($_POST['action'] ?? '');
    $id     = filter_var($_POST['produk_id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    $qty    = filter_var($_POST['qty'] ?? 1, FILTER_VALIDATE_INT);
    $qty    = $qty === false ? 1 : $qty;

    switch ($action) {
        case 'tambah':
            if ($id === false) {
                flash('danger', 'Produk tidak valid.');
                break;
            }
            // Pastikan produk benar-benar ada di database
            $cek = $pdo->prepare('SELECT nama FROM produk WHERE id = :id');
            $cek->execute([':id' => $id]);
            $nama = $cek->fetchColumn();

            if ($nama === false) {
                flash('danger', 'Produk tidak ditemukan.');
            } else {
                $_SESSION['cart'][$id] = min(MAX_QTY, ($_SESSION['cart'][$id] ?? 0) + max(1, $qty));
                flash('success', '"' . $nama . '" ditambahkan ke keranjang.');
            }
            redirect(safeBack((string) ($_POST['kembali'] ?? '')));

        case 'ubah':
            if ($id !== false && isset($_SESSION['cart'][$id])) {
                if ($qty <= 0) {
                    unset($_SESSION['cart'][$id]);
                    flash('info', 'Item dihapus dari keranjang.');
                } else {
                    $_SESSION['cart'][$id] = min(MAX_QTY, $qty);
                    flash('success', 'Jumlah diperbarui.');
                }
            }
            break;

        case 'hapus':
            if ($id !== false) {
                unset($_SESSION['cart'][$id]);
                flash('info', 'Item dihapus dari keranjang.');
            }
            break;

        case 'kosongkan':
            $_SESSION['cart'] = [];
            flash('info', 'Keranjang dikosongkan.');
            break;
    }

    redirect('keranjang.php');   // Post/Redirect/Get
}

/* =========================================================
 * Tampilkan keranjang: gabungkan data session dengan database
 * ========================================================= */
$items = [];
$total = 0.0;

if (!empty($_SESSION['cart'])) {
    $ids = array_map('intval', array_keys($_SESSION['cart']));
    $placeholder = implode(',', array_fill(0, count($ids), '?'));

    $stmt = $pdo->prepare("SELECT id, nama, harga FROM produk WHERE id IN ($placeholder)");
    $stmt->execute($ids);
    $produkDb = array_column($stmt->fetchAll(), null, 'id');   // di-index berdasarkan id

    foreach ($_SESSION['cart'] as $pid => $jumlah) {
        if (!isset($produkDb[$pid])) {
            // Produk sudah dihapus dari database -> buang dari keranjang
            unset($_SESSION['cart'][$pid]);
            continue;
        }
        $harga    = (float) $produkDb[$pid]['harga'];
        $subtotal = $harga * $jumlah;
        $total   += $subtotal;

        $items[] = [
            'id'       => (int) $pid,
            'nama'     => $produkDb[$pid]['nama'],
            'harga'    => $harga,
            'qty'      => (int) $jumlah,
            'subtotal' => $subtotal,
        ];
    }
}

$judul = 'Keranjang';
require __DIR__ . '/_header.php';
?>

<h1>Keranjang Belanja</h1>

<?php if (empty($items)): ?>
    <div class="kosong">
        Keranjang Anda masih kosong.<br><br>
        <a href="index.php" class="btn btn-primary">Mulai Belanja</a>
    </div>
<?php else: ?>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Produk</th>
                    <th class="num">Harga</th>
                    <th>Jumlah</th>
                    <th class="num">Subtotal</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($items as $it): ?>
                <tr>
                    <td><?= e($it['nama']) ?></td>
                    <td class="num"><?= rupiah($it['harga']) ?></td>
                    <td>
                        <form method="post" class="inline">
                            <?= csrf_field() ?>
                            <input type="hidden" name="action" value="ubah">
                            <input type="hidden" name="produk_id" value="<?= $it['id'] ?>">
                            <input type="number" name="qty" value="<?= $it['qty'] ?>" min="0" max="<?= MAX_QTY ?>">
                            <button type="submit" class="btn btn-light">Ubah</button>
                        </form>
                    </td>
                    <td class="num"><?= rupiah($it['subtotal']) ?></td>
                    <td>
                        <form method="post" class="inline">
                            <?= csrf_field() ?>
                            <input type="hidden" name="action" value="hapus">
                            <input type="hidden" name="produk_id" value="<?= $it['id'] ?>">
                            <button type="submit" class="btn btn-danger">Hapus</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="total">Total: <?= rupiah($total) ?></div>

    <div class="aksi">
        <a href="index.php" class="btn btn-light">&larr; Lanjut Belanja</a>
        <form method="post" class="inline" onsubmit="return confirm('Kosongkan seluruh keranjang?');">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="kosongkan">
            <button type="submit" class="btn btn-danger">Kosongkan Keranjang</button>
        </form>
    </div>
<?php endif; ?>

<?php require __DIR__ . '/_footer.php'; ?>
