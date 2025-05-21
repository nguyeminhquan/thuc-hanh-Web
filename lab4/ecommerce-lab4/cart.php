<?php
require_once 'auth.php';
require_once 'config.php';
require_once 'products.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

// Initialize cart if not exists
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

$cart_items = [];
$total = 0;

// Get cart items details
foreach ($_SESSION['cart'] as $product_id => $quantity) {
    $product = getProductById($product_id);
    if ($product) {
        $cart_items[] = [
            'id' => $product['id'],
            'name' => $product['name'],
            'price' => $product['price'],
            'quantity' => $quantity,
            'available' => $product['quantity'],
            'image' => $product['image_url']
        ];
        $total += $product['price'] * $quantity;
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Giỏ hàng - Shop Capybara</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-5">
        <h2 class="mb-4">Giỏ hàng</h2>
        
        <?php if (empty($cart_items)): ?>
            <div class="alert alert-info">
                Giỏ hàng của bạn đang trống. <a href="index.php">Tiếp tục mua sắm</a>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Sản phẩm</th>
                            <th>Giá</th>
                            <th>Số lượng</th>
                            <th>Tổng</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($cart_items as $item): ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <img src="<?php echo htmlspecialchars($item['image']); ?>" 
                                             alt="<?php echo htmlspecialchars($item['name']); ?>"
                                             style="width: 50px; height: 50px; object-fit: cover;"
                                             class="me-3">
                                        <?php echo htmlspecialchars($item['name']); ?>
                                    </div>
                                </td>
                                <td><?php echo number_format($item['price']); ?> VND</td>
                                <td>
                                    <div class="input-group" style="width: 120px;">
                                        <button class="btn btn-outline-secondary btn-sm update-quantity" 
                                                data-product-id="<?php echo $item['id']; ?>"
                                                data-action="decrease">-</button>
                                        <input type="number" class="form-control form-control-sm text-center" 
                                               value="<?php echo $item['quantity']; ?>"
                                               min="1" max="<?php echo $item['available']; ?>"
                                               data-product-id="<?php echo $item['id']; ?>">
                                        <button class="btn btn-outline-secondary btn-sm update-quantity"
                                                data-product-id="<?php echo $item['id']; ?>"
                                                data-action="increase">+</button>
                                    </div>
                                </td>
                                <td><?php echo number_format($item['price'] * $item['quantity']); ?> VND</td>
                                <td>
                                    <button class="btn btn-danger btn-sm remove-item" 
                                            data-product-id="<?php echo $item['id']; ?>">Xóa</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="3" class="text-end"><strong>Tổng cộng:</strong></td>
                            <td><strong><?php echo number_format($total); ?> VND</strong></td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
            
            <div class="d-flex justify-content-between mt-4">
                <a href="index.php" class="btn btn-secondary">Tiếp tục mua sắm</a>
                <button class="btn btn-primary" id="checkout-btn">Thanh toán</button>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.querySelectorAll('.update-quantity').forEach(button => {
            button.addEventListener('click', function() {
                const productId = this.dataset.productId;
                const input = document.querySelector(`input[data-product-id="${productId}"]`);
                const currentValue = parseInt(input.value);
                const maxValue = parseInt(input.max);
                
                if (this.dataset.action === 'increase' && currentValue < maxValue) {
                    input.value = currentValue + 1;
                } else if (this.dataset.action === 'decrease' && currentValue > 1) {
                    input.value = currentValue - 1;
                }
                
                updateCart(productId, input.value);
            });
        });

        document.querySelectorAll('input[type="number"]').forEach(input => {
            input.addEventListener('change', function() {
                const productId = this.dataset.productId;
                const value = parseInt(this.value);
                const max = parseInt(this.max);
                
                if (value > max) {
                    this.value = max;
                    alert('Số lượng vượt quá số lượng có sẵn!');
                } else if (value < 1) {
                    this.value = 1;
                }
                
                updateCart(productId, this.value);
            });
        });

        document.querySelectorAll('.remove-item').forEach(button => {
            button.addEventListener('click', function() {
                const productId = this.dataset.productId;
                removeFromCart(productId);
            });
        });

        document.getElementById('checkout-btn').addEventListener('click', function() {
            window.location.href = 'checkout.php';
        });

        function updateCart(productId, quantity) {
            fetch('update_cart.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    product_id: productId,
                    quantity: quantity
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert(data.message || 'Có lỗi xảy ra!');
                }
            });
        }

        function removeFromCart(productId) {
            fetch('update_cart.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    product_id: productId,
                    quantity: 0
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert(data.message || 'Có lỗi xảy ra!');
                }
            });
        }
    </script>
</body>
</html> 