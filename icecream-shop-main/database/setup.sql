-- Create database
CREATE DATABASE IF NOT EXISTS ic_db;
USE ic_db;

-- Table: admin
CREATE TABLE IF NOT EXISTS admin (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert default admin user
INSERT INTO admin (username, password) VALUES ('admin', 'krish')
ON DUPLICATE KEY UPDATE username = username;

-- Table: users
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Table: products (with category and type columns)
CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_name VARCHAR(100) NOT NULL,
    category VARCHAR(50),
    type VARCHAR(50),
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

-- Insert sample products with categories and types
INSERT INTO products (product_name, category, type, price, description, ingredients, image) VALUES
('Strawberry Delight', 'Fruit', 'Ice Cream', 200.00, 'A rich and creamy strawberry ice cream made with fresh strawberries.', 'Fresh strawberries, cream, milk, sugar', '../image/strawberry.jpeg'),
('Pink Vanilla', 'Classic', 'Ice Cream', 150.00, 'Classic vanilla ice cream with a delightful pink twist.', 'Vanilla extract, cream, milk, sugar, natural pink coloring', '../image/pinkvanilla.jpeg'),
('Cherry Blossom', 'Fruit', 'Ice Cream', 250.00, 'A delightful cherry ice cream with a hint of blossom flavor.', 'Fresh cherries, cherry blossom essence, cream, milk, sugar', '../image/cherryblossom.webp'),
('Chocolate Fudge', 'Chocolate', 'Ice Cream', 220.00, 'Rich and creamy chocolate ice cream with chocolate fudge swirls.', 'Belgian chocolate, cocoa powder, cream, milk, sugar', '../image/chocolate_fudge.jpg'),
('Mint Chocolate Chip', 'Chocolate', 'Ice Cream', 210.00, 'Refreshing mint ice cream loaded with chocolate chips.', 'Fresh mint leaves, chocolate chips, cream, milk, sugar', '../image/mint_chocolate.jpg'),
('Butter Pecan', 'Nut', 'Ice Cream', 230.00, 'Buttery vanilla ice cream with roasted pecans.', 'Roasted pecans, butter, vanilla extract, cream, milk', '../image/butter_pecan.jpg'),
('Mango Sorbet', 'Fruit', 'Sorbet', 180.00, 'Refreshing dairy-free mango sorbet made with real mangoes.', 'Fresh mangoes, sugar, water, lime juice', '../image/mango_sorbet.jpg'),
('Cookies and Cream', 'Chocolate', 'Ice Cream', 240.00, 'Vanilla ice cream with crushed chocolate cookie pieces.', 'Chocolate cookies, vanilla extract, cream, milk, sugar', '../image/cookies_cream.jpg'),
('Blueberry Cheesecake', 'Fruit', 'Premium', 280.00, 'Creamy cheesecake ice cream with blueberry swirls and graham cracker pieces.', 'Cream cheese, fresh blueberries, graham crackers, cream, milk', '../image/blueberry_cheesecake.jpg'),
('Salted Caramel', 'Classic', 'Premium', 260.00, 'Sweet and salty caramel ice cream with a smooth texture.', 'Homemade caramel, sea salt, cream, milk, sugar', '../image/salted_caramel.jpg'),
('Dark Chocolate', 'Chocolate', 'Gelato', 270.00, 'Intense dark chocolate gelato made with 70% cocoa.', 'Dark chocolate, cocoa powder, milk, sugar, egg yolks', '../image/dark_chocolate.jpg'),
('Pistachio', 'Nut', 'Gelato', 290.00, 'Authentic Italian pistachio gelato with real pistachios.', 'Pistachio paste, roasted pistachios, milk, cream, sugar', '../image/pistachio.jpg'),
('Strawberry Sorbet', 'Fruit', 'Sorbet', 170.00, 'Refreshing strawberry sorbet made with fresh strawberries.', 'Fresh strawberries, sugar, water, lemon juice', '../image/strawberry_sorbet.jpg'),
('Vanilla Bean', 'Classic', 'Ice Cream', 190.00, 'Rich vanilla ice cream with real vanilla bean specks.', 'Madagascar vanilla beans, cream, milk, sugar, egg yolks', '../image/vanilla_bean.jpg'),
('Chocolate Chip', 'Chocolate', 'Ice Cream', 210.00, 'Creamy vanilla ice cream with chocolate chips.', 'Chocolate chips, vanilla extract, cream, milk, sugar', '../image/chocolate_chip.jpg');

-- Insert sample users
INSERT INTO users (username, email, password) VALUES
('john_doe', 'john@example.com', 'password123'),
('jane_smith', 'jane@example.com', 'password123'),
('mike_wilson', 'mike@example.com', 'mike123'),
('sarah_johnson', 'sarah@example.com', 'sarah456'),
('david_brown', 'david@example.com', 'david789')
ON DUPLICATE KEY UPDATE username = username;

-- Insert sample orders
INSERT INTO orders (name, phone, address, total_price, order_date, product_name, quantity, price, status) VALUES
('John Doe', '9876543210', '123 Main Street, Palayamkottai, Tirunelveli', 400.00, CURDATE(), 'Strawberry Delight', 2, 200.00, 'Pending'),
('Jane Smith', '9876543211', '456 Park Avenue, Tirunelveli', 250.00, CURDATE(), 'Cherry Blossom', 1, 250.00, 'Shipped'),
('Mike Wilson', '9876543212', '789 Lake View Road, Tirunelveli', 440.00, DATE_SUB(CURDATE(), INTERVAL 1 DAY), 'Chocolate Fudge', 2, 220.00, 'Delivered'),
('Sarah Johnson', '9876543213', '321 Hill Street, Palayamkottai', 230.00, DATE_SUB(CURDATE(), INTERVAL 2 DAY), 'Butter Pecan', 1, 230.00, 'Delivered'),
('David Brown', '9876543214', '654 River Side, Tirunelveli', 360.00, DATE_SUB(CURDATE(), INTERVAL 3 DAY), 'Mango Sorbet', 2, 180.00, 'Shipped');

-- Create indexes for better performance
CREATE INDEX idx_order_date ON orders(order_date);
CREATE INDEX idx_product_category ON products(category);
CREATE INDEX idx_product_type ON products(type);
CREATE INDEX idx_product_name ON products(product_name);