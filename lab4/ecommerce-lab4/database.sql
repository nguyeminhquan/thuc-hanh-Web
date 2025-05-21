-- Create database
CREATE DATABASE IF NOT EXISTS ecommerce_db;
USE ecommerce_db;

-- Create products table
CREATE TABLE IF NOT EXISTS products (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    quantity INT NOT NULL,
    image_url VARCHAR(255),
    discount_percent INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create users table
CREATE TABLE IF NOT EXISTS users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create orders table
CREATE TABLE IF NOT EXISTS orders (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    order_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    total_amount DECIMAL(10,2) NOT NULL,
    status VARCHAR(50) DEFAULT 'pending',
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Create order_items table
CREATE TABLE IF NOT EXISTS order_items (
    id INT PRIMARY KEY AUTO_INCREMENT,
    order_id INT,
    product_id INT,
    quantity INT NOT NULL,
    price_at_time DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id),
    FOREIGN KEY (product_id) REFERENCES products(id)
);

-- Insert sample products
INSERT INTO products (name, price, quantity, image_url, discount_percent) VALUES
('Cabipara', 700000, 10, 'https://th.bing.com/th/id/OIP.OkNi_hMgMatZhz2gYSYuIAHaHa?w=220&h=219&c=7&r=0&o=5&dpr=1.4&pid=1.7', 10),
('Cabipara Special', 800000, 5, 'https://th.bing.com/th/id/OIP.TqaBmbPp6FsvrJWMsn5hlAHaI5?w=165&h=198&c=7&r=0&o=5&dpr=1.4&pid=1.7', 10),
('Cabipara Premium', 900000, 8, 'https://dongvat.edu.vn/upload/2025/01/capybara-meme-29.webp', 10),
('Cabipara Deluxe', 1000000, 3, 'https://dongvat.edu.vn/upload/2025/01/capybara-meme-30.webp', 10); 