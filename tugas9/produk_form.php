<?php
declare(strict_types=1);

require_once __DIR__ . '/helpers.php';

$pdo = getConnection();

/* ---------------------------------------------------------
 * Mode: tanpa id = CREATE, dengan id = UPDATE
 * id dibaca dari POST (saat submit) atau GET (saat buka form)
 * --------------------------------------------------------- */
$idMentah = $_SERVER['REQUEST_METHOD'] === 'POST' ? ($_POST['id'] ?? null) : ($_GET['id'] ?? null);
$id = null;
if ($idMentah !== null && $idMentah !== '') {
    $id = filter_var($idMentah, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    if ($id === false) {
        flash('danger', 'ID produk tidak valid.');
        redirect('index.php');
    }
}
$modeEdit = $id !== null;

/* Nilai awal form */
$data = ['nama' => '', 'harga' => '', 'deskripsi' => '', 'kategori_id' => ''];

if ($modeEdit && $_SERVER['REQUEST_METHOD'] !== 'POST') {
    $stmt = $pdo->prepare('SELECT nama, harga, deskripsi, kategori_id FROM produk WHERE id = :id');
    $stmt->execute([':id' => $id]);
    $row = $stmt->fetch();

    if (!$row) {
        flash('danger', 'Produk tidak ditemukan.');
        redirect('index.php');
    }
    $data = [
        'nama'        => $row['nama'],
        'harga'       => (string) (float) $row['harga'],   // 350000.00 -> "350000"
        'deskripsi'   => $row['deskripsi'],
        'kategori_id' => (string) ($row['kategori_id'] ?? ''),
    ];
}

$kategori = $pdo->query('SELECT id, nama FROM kategori ORDER BY nama')->fetchAll();
$idKategoriValid = array_map('intval', array_column($kategori, 'id'));
$errors = [];

/* ---------------------------------------------------------
 * Proses submit (Create / Update)
 * --------------------------------------------------------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();

    $data = [
        'nama'        => trim((string) ($_POST['nama'] ?? '')),
        'harga'       => trim((string) ($_POST['harga'] ?? '')),
        'deskripsi'   => trim((string) ($_POST['deskripsi'] ?? '')),
        'kategori_id' => trim((string) ($_POST['kategori_id'] ?? '')),
    ];

    // Validasi nama
    if ($data['nama'] === '') {
        $errors['nama'] = 'Nama produk wajib diisi.';
    } elseif (mb_strlen($data['nama']) > 100) {
        $errors['nama'] = 'Nama produk maksimal 100 karakter.';
    }

    // Validasi harga: angka positif, maksimal 2 desimal (pakai titik)
    if ($data['harga'] === '') {
        $errors['harga'] = 'Harga wajib diisi.';
    } elseif (!preg_match('/^\d{1,10}(\.\d{1,2})?$/', $data['harga'])) {
        $errors['harga'] = 'Harga harus berupa angka (maks. 2 desimal, gunakan titik).';
    } elseif ((float) $data['harga'] <= 0) {
        $errors['harga'] = 'Harga harus lebih besar dari 0.';
    }

    // Validasi deskripsi
    if ($data['deskripsi'] === '') {
        $errors['deskripsi'] = 'Deskripsi wajib diisi.';
    }

    // Validasi kategori: boleh kosong, tapi jika diisi harus ada di database
    $kategoriDb = null;
    if ($data['kategori_id'] !== '') {
        $kid = filter_var($data['kategori_id'], FILTER_VALIDATE_INT);
        if ($kid === false || !in_array($kid, $idKategoriValid, true)) {
            $errors['kategori_id'] = 'Kategori tidak valid.';
        } else {
            $kategoriDb = $kid;
        }
    }

    if (empty($errors)) {
        try {
            $param = [
                ':kategori_id' => $kategoriDb,
                ':nama'        => $data['nama'],
                ':harga'       => $data['harga'],
                ':deskripsi'   => $data['deskripsi'],
            ];

            if ($modeEdit) {
                // UPDATE
                $stmt = $pdo->prepare(
                    'UPDATE produk
                        SET kategori_id = :kategori_id, nama = :nama,
                            harga = :harga, deskripsi = :deskripsi
                      WHERE id = :id'
                );
                $stmt->execute($param + [':id' => $id]);
                flash('success', 'Produk berhasil diperbarui.');
            } else {
                // CREATE
                $stmt = $pdo->prepare(
                    'INSERT INTO produk (kategori_id, nama, harga, deskripsi)
                     VALUES (:kategori_id, :nama, :harga, :deskripsi)'
                );
                $stmt->execute($param);
                flash('success', 'Produk baru berhasil ditambahkan.');
            }
            redirect('index.php');   // Post/Redirect/Get: refresh tidak menyimpan dua kali

        } catch (PDOException $ex) {
            error_log($ex->getMessage());
            $errors['db'] = 'Terjadi kesalahan saat menyimpan data. Silakan coba lagi.';
        }
    }
}

$judul = $modeEdit ? 'Edit Produk' : 'Tambah Produk';
require __DIR__ . '/_header.php';
?>

<h1><?= e($judul) ?></h1>

<div class="panel">
    <?php if (isset($errors['db'])): ?>
        <div class="alert alert-danger"><?= e($errors['db']) ?></div>
    <?php endif; ?>

    <form method="post" action="" novalidate>
        <?= csrf_field() ?>
        <?php if ($modeEdit): ?>
            <input type="hidden" name="id" value="<?= (int) $id ?>">
        <?php endif; ?>

        <label for="nama">Nama Produk</label>
        <input type="text" id="nama" name="nama" value="<?= e($data['nama']) ?>"
               class="<?= isset($errors['nama']) ? 'invalid' : '' ?>">
        <?php if (isset($errors['nama'])): ?><div class="error"><?= e($errors['nama']) ?></div><?php endif; ?>

        <label for="kategori_id">Kategori</label>
        <select id="kategori_id" name="kategori_id" class="<?= isset($errors['kategori_id']) ? 'invalid' : '' ?>">
            <option value="">- Tanpa kategori -</option>
            <?php foreach ($kategori as $kat): ?>
                <option value="<?= (int) $kat['id'] ?>"
                    <?= $data['kategori_id'] === (string) $kat['id'] ? 'selected' : '' ?>>
                    <?= e($kat['nama']) ?>
                </option>
            <?php endforeach; ?>
        </select>
        <?php if (isset($errors['kategori_id'])): ?><div class="error"><?= e($errors['kategori_id']) ?></div><?php endif; ?>

        <label for="harga">Harga (Rp)</label>
        <input type="number" id="harga" name="harga" step="0.01" min="0" value="<?= e($data['harga']) ?>"
               class="<?= isset($errors['harga']) ? 'invalid' : '' ?>">
        <?php if (isset($errors['harga'])): ?><div class="error"><?= e($errors['harga']) ?></div><?php endif; ?>

        <label for="deskripsi">Deskripsi</label>
        <textarea id="deskripsi" name="deskripsi" rows="4"
                  class="<?= isset($errors['deskripsi']) ? 'invalid' : '' ?>"><?= e($data['deskripsi']) ?></textarea>
        <?php if (isset($errors['deskripsi'])): ?><div class="error"><?= e($errors['deskripsi']) ?></div><?php endif; ?>

        <div class="aksi" style="margin-top:1.25rem">
            <button type="submit" class="btn btn-primary"><?= $modeEdit ? 'Simpan Perubahan' : 'Simpan Produk' ?></button>
            <a href="index.php" class="btn btn-light">Batal</a>
        </div>
    </form>
</div>

<?php require __DIR__ . '/_footer.php'; ?>
