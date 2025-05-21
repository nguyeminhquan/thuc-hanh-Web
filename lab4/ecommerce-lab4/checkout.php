<?php
require_once 'config.php';
require_once 'auth.php';
require_once 'products.php';

// Check if user is logged in
if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

// Check if cart is empty
if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    header('Location: cart.php');
    exit;
}

$error = '';
$success = '';

// Process checkout
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Get cart items
        $items = [];
        foreach ($_SESSION['cart'] as $product_id => $quantity) {
            $product = getProductById($product_id);
            if (!$product) {
                throw new Exception("Sản phẩm không tồn tại!");
            }
            if ($product['quantity'] < $quantity) {
                throw new Exception("Sản phẩm {$product['name']} chỉ còn {$product['quantity']} sản phẩm!");
            }
            $items[] = [
                'product_id' => $product_id,
                'quantity' => $quantity,
                'price' => $product['price'] * (1 - $product['discount_percent']/100)
            ];
        }

        // Create order
        $order_id = createOrder($_SESSION['user_id'], $items);
        if (!$order_id) {
            throw new Exception("Không thể tạo đơn hàng!");
        }

        // Clear cart
        $_SESSION['cart'] = [];
        
        $success = "Đặt hàng thành công! Mã đơn hàng: " . $order_id;
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

// Get cart items for display
$cart_items = [];
$total = 0;
foreach ($_SESSION['cart'] as $product_id => $quantity) {
    $product = getProductById($product_id);
    if ($product) {
        $price = $product['price'] * (1 - $product['discount_percent']/100);
        $cart_items[] = [
            'id' => $product['id'],
            'name' => $product['name'],
            'price' => $price,
            'quantity' => $quantity,
            'subtotal' => $price * $quantity
        ];
        $total += $price * $quantity;
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thanh toán - Shop Capybara</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="index.php">Shop Capybara</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav">
                    <li class="nav-item"><a class="nav-link" href="index.php">Trang chủ</a></li>
                </ul>
                <div class="ms-auto">
                    <span class="text-white me-3">Xin chào, <?php echo htmlspecialchars($_SESSION['username']); ?></span>
                    <a href="cart.php" class="btn btn-warning me-2">Giỏ hàng</a>
                    <a href="logout.php" class="btn btn-outline-light">Đăng xuất</a>
                </div>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success">
                <?php echo htmlspecialchars($success); ?>
                <div class="mt-3">
                    <a href="index.php" class="btn btn-primary">Tiếp tục mua sắm</a>
                </div>
            </div>
        <?php else: ?>
            <h2>Thanh toán</h2>
            <div class="row">
                <div class="col-md-8">
                    <div class="card mb-4">
                        <div class="card-header">
                            <h4>Thông tin đơn hàng</h4>
                        </div>
                        <div class="card-body">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Sản phẩm</th>
                                        <th>Giá</th>
                                        <th>Số lượng</th>
                                        <th>Tổng</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($cart_items as $item): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($item['name']); ?></td>
                                        <td><?php echo number_format($item['price']); ?> VND</td>
                                        <td><?php echo $item['quantity']; ?></td>
                                        <td><?php echo number_format($item['subtotal']); ?> VND</td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="3" class="text-end"><strong>Tổng cộng:</strong></td>
                                        <td><strong><?php echo number_format($total); ?> VND</strong></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header">
                            <h4>Xác nhận đơn hàng</h4>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="">
                                <div class="mb-3">
                                    <label class="form-label">Tổng tiền:</label>
                                    <h3 class="text-primary"><?php echo number_format($total); ?> VND</h3>
                                </div>
                                <button type="submit" class="btn btn-primary w-100">Xác nhận đặt hàng</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 