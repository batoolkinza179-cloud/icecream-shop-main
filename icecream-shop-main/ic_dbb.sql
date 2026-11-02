-- Create database
CREATE DATABASE IF NOT EXISTS ic_db;
USE ic_db;

-- Table: admin
CREATE TABLE IF NOT EXISTS admin (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,  -- Now stores plain text
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert default admin user (plain text password)
INSERT INTO admin (username, password) VALUES ('admin', 'krish');

-- Table: users
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,  -- Now stores plain text
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Table: products
CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_name VARCHAR(100) NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    description TEXT,
    ingredients TEXT,
    image VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Table: orders
CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    address TEXT NOT NULL,
    total_price DECIMAL(10,2) NOT NULL,
    order_date DATE NOT NULL,
    product_name VARCHAR(100) NOT NULL,
    quantity INT NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    status ENUM('Pending','Shipped','Delivered') DEFAULT 'Pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert sample products
INSERT INTO products (product_name, price, description, ingredients, image) VALUES
('Strawberry', 200.00, 'A rich and creamy strawberry ice cream made with fresh strawberries.', 'Fresh strawberries, cream, milk, sugar', '../image/strawberry.jpeg'),
('Pink Vanilla', 150.00, 'Classic vanilla ice cream with a delightful pink twist.', 'Vanilla extract, cream, milk, sugar, natural pink coloring', '../image/pinkvanilla.jpeg'),
('Cherry Blossom', 250.00, 'A delightful cherry ice cream with a hint of blossom flavor.', 'Fresh cherries, cherry blossom essence, cream, milk, sugar', '../image/cherryblossom.webp');

-- Insert sample users (plain text passwords)
INSERT INTO users (username, email, password) VALUES
('john_doe', 'john@example.com', 'password123'),
('jane_smith', 'jane@example.com', 'password123'),
('mike_wilson', 'mike@example.com', 'mike123'),
('sarah_johnson', 'sarah@example.com', 'sarah456'),
('david_brown', 'david@example.com', 'david789');

-- Insert sample orders
INSERT INTO orders (name, phone, address, total_price, order_date, product_name, quantity, price, status) VALUES
('John Doe', '9876543210', '123 Main Street, Palayamkottai, Tirunelveli', 400.00, CURDATE(), 'Strawberry', 2, 200.00, 'Pending'),
('Jane Smith', '9876543211', '456 Park Avenue, Tirunelveli', 250.00, CURDATE(), 'Cherry Blossom', 1, 250.00, 'Shipped');