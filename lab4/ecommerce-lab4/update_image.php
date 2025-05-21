<?php
require_once 'config.php';

try {
    $conn = getDBConnection();
    
    // Update image URL for Cabipara Premium
    $stmt = $conn->prepare("UPDATE products SET image_url = ? WHERE name = 'Cabipara Premium'");
    $image_url = 'https://dongvat.edu.vn/upload/2025/01/capybara-meme-29.webp';
    $stmt->execute([$image_url]);
    
    if ($stmt->rowCount() > 0) {
        echo "Đã cập nhật ảnh thành công!";
    } else {
        echo "Không tìm thấy sản phẩm Cabipara Premium";
    }
} catch(PDOException $e) {
    echo "Lỗi: " . $e->getMessage();
}
?> 