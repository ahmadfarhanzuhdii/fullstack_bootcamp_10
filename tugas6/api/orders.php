<?php
require_once __DIR__ . '/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method tidak diizinkan.']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$userId = isset($input['user_id']) ? (int)$input['user_id'] : 0;
$items = $input['items'] ?? [];

if ($userId <= 0 || !is_array($items) || count($items) === 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Data checkout tidak lengkap.']);
    exit;
}

try {
    $pdo->beginTransaction();

    $userStmt = $pdo->prepare("SELECT id FROM users WHERE id = ?");
    $userStmt->execute([$userId]);

    if (!$userStmt->fetch()) {
        throw new Exception('User tidak ditemukan.');
    }

    $productStmt = $pdo->prepare("
        SELECT id, harga, stok
        FROM products
        WHERE id = ?
        FOR UPDATE
    ");

    $insertOrder = $pdo->prepare("
        INSERT INTO orders (user_id, product_id, quantity, total)
        VALUES (?, ?, ?, ?)
    ");

    $updateStock = $pdo->prepare("
        UPDATE products
        SET stok = stok - ?
        WHERE id = ?
    ");

    $orderIds = [];

    foreach ($items as $item) {
        $productId = (int)($item['product_id'] ?? 0);
        $quantity = (int)($item['quantity'] ?? 0);

        if ($productId <= 0 || $quantity <= 0) {
            throw new Exception('Produk atau quantity tidak valid.');
        }

        $productStmt->execute([$productId]);
        $product = $productStmt->fetch();

        if (!$product) {
            throw new Exception("Produk ID $productId tidak ditemukan.");
        }

        if ((int)$product['stok'] < $quantity) {
            throw new Exception("Stok produk ID $productId tidak mencukupi.");
        }

        $total = (float)$product['harga'] * $quantity;

        $insertOrder->execute([
            $userId,
            $productId,
            $quantity,
            $total
        ]);

        $orderIds[] = (int)$pdo->lastInsertId();

        $updateStock->execute([$quantity, $productId]);
    }

    $pdo->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Order berhasil disimpan.',
        'order_ids' => $orderIds
    ]);

} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
