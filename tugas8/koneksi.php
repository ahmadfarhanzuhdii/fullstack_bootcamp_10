<?php
declare(strict_types=1);

/**
 * Koneksi database (PDO) - satu instance dipakai bersama.
 * Pemakaian:
 *   require_once __DIR__ . '/koneksi.php';
 *   $pdo = getConnection();
 */
function getConnection(): PDO
{
    static $pdo = null;   // disimpan antar pemanggilan (singleton sederhana)

    if ($pdo === null) {
        $host = 'localhost';
        $db   = 'toko_db';
        $user = 'root';
        $pass = '';

        try {
            $pdo = new PDO(
                "mysql:host=$host;dbname=$db;charset=utf8mb4",
                $user,
                $pass,
                [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false, // prepared statement asli
                ]
            );
        } catch (PDOException $e) {
            error_log('Koneksi DB gagal: ' . $e->getMessage());
            http_response_code(500);
            exit('Layanan sedang bermasalah. Silakan coba beberapa saat lagi.');
        }
    }

    return $pdo;
}
