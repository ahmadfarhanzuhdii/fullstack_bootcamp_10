<?php
declare(strict_types=1);

/**
 * Helper bersama untuk semua halaman.
 * Di-include di baris pertama setiap halaman: require_once __DIR__ . '/helpers.php';
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/koneksi.php';

/** Escape output HTML (cegah XSS). */
function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

/** Format angka menjadi Rupiah. */
function rupiah(float|int|string $angka): string
{
    return 'Rp ' . number_format((float) $angka, 0, ',', '.');
}

/** Redirect lalu hentikan eksekusi. */
function redirect(string $url): never
{
    header('Location: ' . $url);
    exit;
}

/** Halaman ini hanya boleh diakses dengan method POST. */
function requirePost(): void
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        exit('Method tidak diizinkan.');
    }
}

/* ---------------------- CSRF ---------------------- */

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}

function csrf_verify(): void
{
    $kirim = $_POST['csrf_token'] ?? '';
    if (!is_string($kirim) || !hash_equals($_SESSION['csrf_token'] ?? '', $kirim)) {
        http_response_code(419);
        exit('Sesi tidak valid. Silakan kembali dan muat ulang halaman.');
    }
}

/* ---------------------- Flash message ---------------------- */

function flash(string $type, string $pesan): void
{
    $_SESSION['flash'][] = ['type' => $type, 'pesan' => $pesan];
}

function pullFlash(): array
{
    $pesan = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $pesan;
}

/* ---------------------- Cart ---------------------- */

/** Total jumlah item (qty) di keranjang. */
function cartCount(): int
{
    return array_sum($_SESSION['cart'] ?? []);
}
