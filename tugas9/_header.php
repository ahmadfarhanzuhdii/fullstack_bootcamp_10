<?php
/** Variabel opsional: $judul (string) */
$judul ??= 'Toko Online';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($judul) ?> - Toko Online</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: system-ui, sans-serif; background:#f4f5f7; margin:0; color:#1f2937; }
        a { color:inherit; }

        .topbar { background:#2563eb; color:#fff; padding:.9rem 2rem; display:flex;
                  justify-content:space-between; align-items:center; }
        .topbar a { color:#fff; text-decoration:none; }
        .brand { font-size:1.3rem; font-weight:700; }
        .topbar nav { display:flex; gap:1.2rem; }
        .pill { background:#fff; color:#2563eb; border-radius:999px; padding:0 .55rem;
                font-size:.8rem; font-weight:700; }

        main { max-width:1100px; margin:auto; padding:1.5rem; }
        .toolbar { display:flex; justify-content:space-between; align-items:center;
                   flex-wrap:wrap; gap:.75rem; margin-bottom:1.25rem; }
        h1 { margin:0 0 1rem; font-size:1.4rem; }

        .alert { padding:.75rem 1rem; border-radius:6px; margin-bottom:1rem; }
        .alert-success { background:#dff0d8; color:#2d6a2d; }
        .alert-danger  { background:#f8d7da; color:#842029; }
        .alert-info    { background:#dbeafe; color:#1e40af; }

        .btn { display:inline-block; padding:.45rem .9rem; border:1px solid transparent; border-radius:6px;
               font:inherit; font-size:.9rem; cursor:pointer; text-decoration:none; }
        .btn-primary { background:#2563eb; color:#fff; }
        .btn-primary:hover { background:#1d4ed8; }
        .btn-light  { background:#fff; border-color:#d1d5db; color:#374151; }
        .btn-light:hover { border-color:#2563eb; }
        .btn-danger { background:#fff; border-color:#dc2626; color:#dc2626; }
        .btn-danger:hover { background:#dc2626; color:#fff; }
        form.inline { display:inline; margin:0; }

        .filter { display:flex; flex-wrap:wrap; gap:.5rem; }
        .filter a { padding:.4rem .9rem; border-radius:999px; background:#fff; border:1px solid #d1d5db;
                    text-decoration:none; font-size:.9rem; }
        .filter a:hover { border-color:#2563eb; }
        .filter a.aktif { background:#2563eb; color:#fff; border-color:#2563eb; }

        .grid { display:grid; grid-template-columns:repeat(auto-fill, minmax(240px, 1fr)); gap:1rem; }
        .card { background:#fff; border-radius:8px; padding:1.1rem; display:flex; flex-direction:column;
                box-shadow:0 1px 4px rgba(0,0,0,.08); }
        .badge { align-self:flex-start; font-size:.75rem; background:#e0e7ff; color:#3730a3;
                 padding:.15rem .6rem; border-radius:999px; margin-bottom:.6rem; }
        .card h2 { font-size:1.05rem; margin:0 0 .4rem; }
        .card p  { font-size:.9rem; color:#6b7280; flex:1; margin:0 0 .8rem; }
        .harga { font-weight:700; color:#2563eb; font-size:1.1rem; margin-bottom:.75rem; }
        .aksi { display:flex; flex-wrap:wrap; gap:.4rem; }
        .kosong { background:#fff; padding:2rem; text-align:center; border-radius:8px; color:#6b7280; }

        .panel { background:#fff; border-radius:8px; padding:1.5rem 2rem; max-width:520px;
                 box-shadow:0 1px 4px rgba(0,0,0,.08); }
        .panel label { display:block; margin:1rem 0 .3rem; font-weight:600; }
        .panel input, .panel textarea, .panel select { width:100%; padding:.6rem; border:1px solid #ccc;
                 border-radius:6px; font:inherit; }
        .invalid { border-color:#d9534f !important; }
        .error { color:#d9534f; font-size:.85rem; margin-top:.25rem; }

        table { width:100%; border-collapse:collapse; background:#fff; border-radius:8px; overflow:hidden;
                box-shadow:0 1px 4px rgba(0,0,0,.08); }
        th, td { padding:.75rem 1rem; text-align:left; border-bottom:1px solid #eee; }
        th { background:#f9fafb; font-size:.85rem; color:#6b7280; }
        td.num, th.num { text-align:right; }
        td input[type=number] { width:70px; padding:.35rem; border:1px solid #ccc; border-radius:6px; }
        .total { text-align:right; font-size:1.2rem; font-weight:700; margin:1rem 0; }
        .table-wrap { overflow-x:auto; }
    </style>
</head>
<body>
<header class="topbar">
    <a href="index.php" class="brand">Toko Online</a>
    <nav>
        <a href="index.php">Produk</a>
        <a href="keranjang.php">Keranjang <span class="pill"><?= cartCount() ?></span></a>
    </nav>
</header>

<main>
<?php foreach (pullFlash() as $f): ?>
    <div class="alert alert-<?= e($f['type']) ?>"><?= e($f['pesan']) ?></div>
<?php endforeach; ?>
