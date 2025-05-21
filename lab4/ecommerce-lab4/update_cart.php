<?php
require_once 'auth.php';
require_once 'products.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Vui lòng đăng nhập!']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$product_id = $data['product_id'] ?? 0;
$quantity = intval($data['quantity'] ?? 0);

if ($product_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Sản phẩm không hợp lệ!']);
    exit;
}

$product = getProductById($product_id);
if (!$product) {
    echo json_encode(['success' => false, 'message' => 'Sản phẩm không tồn tại!']);
    exit;
}

if ($quantity > $product['quantity']) {
    echo json_encode(['success' => false, 'message' => 'Số lượng vượt quá số lượng có sẵn!']);
    exit;
}

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

if ($quantity <= 0) {
    unset($_SESSION['cart'][$product_id]);
} else {
    $_SESSION['cart'][$product_id] = $quantity;
}

echo json_encode(['success' => true]);
?>