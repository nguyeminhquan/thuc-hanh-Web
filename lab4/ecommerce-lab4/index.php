<?php
require_once 'config.php';
require_once 'auth.php';
require_once 'products.php';

$products = getAllProducts();

// Calculate cart count
$cart_count = 0;
if (isset($_SESSION['cart'])) {
    $cart_count = array_sum($_SESSION['cart']);
}
?>
<!DOCTYPE html>
<html lang="vi">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Shop Capybara</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" />
  </head>
  <body>
    <style>
        img {
            height: 304px;
            width: 304px;
        }
        *{transition: 1s;}
    </style>

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
      <div class="container-fluid">
        <a class="navbar-brand" href="#">Shop Capybara</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
          <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
          <ul class="navbar-nav">
            <li class="nav-item"><a class="nav-link" href="#">Trang chủ</a></li>
            <li class="nav-item dropdown">
              <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                Tiếng Việt
              </a>
              <ul class="dropdown-menu" aria-labelledby="navbarDropdown">
                <li><a class="dropdown-item" href="#">Tiếng Việt</a></li>
                <li><a class="dropdown-item" href="#">Tiếng Anh</a></li>
              </ul>
            </li>
            <li class="nav-item"><a class="nav-link" href="#">Liên hệ</a></li>
          </ul>
          <?php if (isLoggedIn()): ?>
            <div class="ms-auto">
              <span class="text-white me-3">Xin chào, <?php echo htmlspecialchars($_SESSION['username']); ?></span>
              <a href="cart.php" class="btn btn-warning me-2">
                Giỏ hàng <span class="badge bg-danger" id="cart-count"><?php echo $cart_count; ?></span>
              </a>
              <a href="logout.php" class="btn btn-outline-light">Đăng xuất</a>
            </div>
          <?php else: ?>
            <div class="ms-auto">
              <a href="login.php" class="btn btn-outline-light me-2">Đăng nhập</a>
              <a href="register.php" class="btn btn-light">Đăng ký</a>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </nav>

    <!-- Thanh chọn danh mục -->
    <div class="d-flex flex-wrap justify-content-center gap-4 p-3 mt-4">
      <button class="btn btn-outline-primary">Hàng chọn giá hời</button>
      <button class="btn btn-outline-warning">Mã giảm giá</button>
      <button class="btn btn-outline-success">Miễn phí ship</button>
      <button class="btn btn-outline-info">Giờ săn Sale</button>
      <button class="btn btn-outline-secondary">Hàng Quốc Tế</button>
      <button class="btn btn-outline-dark">Nạp Thẻ & Dịch Vụ</button>
    </div>

    <!-- Danh sách sản phẩm -->
    <div class="container mt-3">
      <div class="row">
        <?php foreach ($products as $product): ?>
        <div class="col-12 col-md-6 col-lg-3">
          <div class="card mb-5">
            <?php if ($product['discount_percent'] > 0): ?>
            <div class="position-absolute bg-danger text-white px-2" style="top: 10px; left: 10px">
              -<?php echo $product['discount_percent']; ?>%
            </div>
            <?php endif; ?>
            <img src="<?php echo htmlspecialchars($product['image_url']); ?>" 
                 class="card-img-top" 
                 alt="<?php echo htmlspecialchars($product['name']); ?>"
                 onerror="this.src='https://via.placeholder.com/304x304?text=No+Image'" />
            <div class="card-body">
              <h5 class="card-title"><?php echo htmlspecialchars($product['name']); ?></h5>
              <div class="d-flex">
                <p class="text-decoration-line-through text-muted mx-2"><?php echo number_format($product['price']); ?> VND</p>
                <p class="fw-bold"><?php echo number_format($product['price'] * (1 - $product['discount_percent']/100)); ?> VND</p>
              </div>
              <p class="text-<?php echo $product['quantity'] > 0 ? 'success' : 'danger'; ?>">
                <?php echo $product['quantity'] > 0 ? 'Còn lại: ' . $product['quantity'] : 'Hết hàng'; ?>
              </p>
              <?php if ($product['quantity'] > 0): ?>
                <button class="btn btn-primary add-to-cart" data-product-id="<?php echo $product['id']; ?>">Mua ngay</button>
              <?php else: ?>
                <button class="btn btn-secondary" disabled>Hết hàng</button>
              <?php endif; ?>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>

    <!-- Footer -->
    <footer class="bg-dark text-white text-center py-3 mt-4">
      <div>
        <a href="#" class="text-white mx-2">Chính sách bảo hành</a>
        <a href="#" class="text-white mx-2">Chính sách đổi trả</a>
        <a href="#" class="text-white mx-2">Chính sách giao hàng</a>
        <a href="#" class="text-white mx-2">Chính sách bảo mật</a>
      </div>
      <p class="mt-2">Địa chỉ: 123 Đường ABC, Quận 1, TP.HCM</p>
      <p>&copy; 2025 Shop Capybara - All rights reserved.</p>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
      document.querySelectorAll('.add-to-cart').forEach(button => {
        button.addEventListener('click', function() {
          const productId = this.dataset.productId;
          console.log('Adding product to cart:', productId); // Debug log
          
          fetch('add_to_cart.php', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
            },
            body: JSON.stringify({ product_id: productId })
          })
          .then(response => {
            console.log('Response status:', response.status); // Debug log
            return response.json();
          })
          .then(data => {
            console.log('Response data:', data); // Debug log
            if (data.success) {
              document.getElementById('cart-count').textContent = data.cart_count;
              alert(data.message || 'Đã thêm vào giỏ hàng!');
            } else {
              alert(data.message || 'Có lỗi xảy ra!');
            }
          })
          .catch(error => {
            console.error('Error:', error); // Debug log
            alert('Có lỗi xảy ra khi thêm vào giỏ hàng!');
          });
        });
      });
    </script>
  </body>
</html> 