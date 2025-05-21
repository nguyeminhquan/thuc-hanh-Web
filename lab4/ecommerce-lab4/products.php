<?php
require_once 'config.php';

function getAllProducts() {
    $conn = getDBConnection();
    try {
        $stmt = $conn->query("SELECT * FROM products");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        error_log("Error getting products: " . $e->getMessage());
        return [];
    }
}

function getProductById($id) {
    $conn = getDBConnection();
    try {
        $stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        error_log("Error getting product: " . $e->getMessage());
        return null;
    }
}

function updateProductQuantity($product_id, $quantity) {
    $conn = getDBConnection();
    try {
        $stmt = $conn->prepare("UPDATE products SET quantity = quantity - ? WHERE id = ? AND quantity >= ?");
        return $stmt->execute([$quantity, $product_id, $quantity]);
    } catch(PDOException $e) {
        error_log("Error updating product quantity: " . $e->getMessage());
        return false;
    }
}

function createOrder($user_id, $items) {
    $conn = getDBConnection();
    try {
        $conn->beginTransaction();
        
        // Create order
        $stmt = $conn->prepare("INSERT INTO orders (user_id, total_amount) VALUES (?, ?)");
        $total = 0;
        foreach ($items as $item) {
            $product = getProductById($item['product_id']);
            $total += $product['price'] * (1 - $product['discount_percent']/100) * $item['quantity'];
        }
        $stmt->execute([$user_id, $total]);
        $order_id = $conn->lastInsertId();
        
        // Add order items
        $stmt = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
        foreach ($items as $item) {
            $product = getProductById($item['product_id']);
            $price = $product['price'] * (1 - $product['discount_percent']/100);
            $stmt->execute([$order_id, $item['product_id'], $item['quantity'], $price]);
            
            // Update product quantity
            if (!updateProductQuantity($item['product_id'], $item['quantity'])) {
                throw new Exception("Không đủ số lượng sản phẩm!");
            }
        }
        
        $conn->commit();
        return $order_id;
    } catch(Exception $e) {
        $conn->rollBack();
        error_log("Error creating order: " . $e->getMessage());
        return false;
    }
}
?> 