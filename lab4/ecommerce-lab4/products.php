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
        // Get current quantity for logging
        $stmt = $conn->prepare("SELECT quantity FROM products WHERE id = ?");
        $stmt->execute([$product_id]);
        $current = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Update quantity
        $stmt = $conn->prepare("UPDATE products SET quantity = quantity - ? WHERE id = ?");
        $result = $stmt->execute([$quantity, $product_id]);
        
        // Verify the update
        if ($result) {
            $stmt = $conn->prepare("SELECT quantity FROM products WHERE id = ?");
            $stmt->execute([$product_id]);
            $after = $stmt->fetch(PDO::FETCH_ASSOC);
            error_log("Product ID: " . $product_id . " - Before: " . $current['quantity'] . " - After: " . $after['quantity']);
        }
        
        return $result;
    } catch(PDOException $e) {
        error_log("Error updating product quantity: " . $e->getMessage());
        return false;
    }
}

function createOrder($user_id, $items) {
    $conn = getDBConnection();
    try {
        $conn->beginTransaction();
        
        // Calculate total first
        $total = 0;
        foreach ($items as $item) {
            $product = getProductById($item['product_id']);
            if (!$product) {
                error_log("Product not found with ID: " . $item['product_id']);
                throw new Exception("Product not found");
            }
            $discounted_price = $product['price'] * (1 - $product['discount_percent']/100);
            $total += $discounted_price * $item['quantity'];
        }
        
        // Create order record
        $stmt = $conn->prepare("INSERT INTO orders (user_id, total_amount, status) VALUES (?, ?, 'pending')");
        if (!$stmt->execute([$user_id, $total])) {
            error_log("Failed to insert order record");
            throw new Exception("Failed to create order");
        }
        $order_id = $conn->lastInsertId();
        
        // Insert order items
        $stmt = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, price_at_time) VALUES (?, ?, ?, ?)");
        foreach ($items as $item) {
            $product = getProductById($item['product_id']);
            $discounted_price = $product['price'] * (1 - $product['discount_percent']/100);
            
            if (!$stmt->execute([$order_id, $item['product_id'], $item['quantity'], $discounted_price])) {
                error_log("Failed to insert order item for product ID: " . $item['product_id']);
                throw new Exception("Failed to create order items");
            }
            
            // Update product quantity
            $update_stmt = $conn->prepare("UPDATE products SET quantity = quantity - ? WHERE id = ?");
            if (!$update_stmt->execute([$item['quantity'], $item['product_id']])) {
                error_log("Failed to update quantity for product ID: " . $item['product_id']);
                throw new Exception("Failed to update product quantity");
            }
        }
        
        $conn->commit();
        error_log("Order created successfully with ID: " . $order_id);
        return $order_id;
        
    } catch (Exception $e) {
        $conn->rollBack();
        error_log("Error creating order: " . $e->getMessage());
        return false;
    }
}
?>