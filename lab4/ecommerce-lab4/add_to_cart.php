<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'auth.php';
require_once 'products.php';

header('Content-Type: application/json');

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    if (!isLoggedIn()) {
        throw new Exception('Vui lòng đăng nhập!');
    }

    $input = file_get_contents('php://input');
    if (!$input) {
        throw new Exception('Không nhận được dữ liệu!');
    }

    $data = json_decode($input, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Dữ liệu không hợp lệ!');
    }

    $product_id = $data['product_id'] ?? 0;
    if ($product_id <= 0) {
        throw new Exception('Sản phẩm không hợp lệ!');
    }

    $product = getProductById($product_id);
    if (!$product) {
        throw new Exception('Sản phẩm không tồn tại!');
    }

    if ($product['quantity'] <= 0) {
        throw new Exception('Sản phẩm đã hết hàng!');
    }

    // Initialize cart if not exists
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    // Add or update quantity in cart
    if (isset($_SESSION['cart'][$product_id])) {
        if ($_SESSION['cart'][$product_id] >= $product['quantity']) {
            throw new Exception('Đã đạt giới hạn số lượng có sẵn!');
        }
        $_SESSION['cart'][$product_id]++;
    } else {
        $_SESSION['cart'][$product_id] = 1;
    }

    // Update product quantity in database
    if (!updateProductQuantity($product_id, 1)) {
        throw new Exception('Không thể cập nhật số lượng sản phẩm!');
    }

    // Calculate total items in cart
    $cart_count = array_sum($_SESSION['cart']);

    echo json_encode([
        'success' => true,
        'message' => 'Đã thêm vào giỏ hàng!',
        'cart_count' => $cart_count
    ]);

} catch (Exception $e) {
    error_log("Add to cart error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?> 