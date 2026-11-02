<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['admin_username'])) {
    header("Location: admin_login.php");
    exit();
}

$message = '';
$error = '';

// Handle Add Product
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'add') {
        $target_dir = "../uploads/";
        if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);
        
        $product_name = trim($_POST['product_name']);
        $price = floatval($_POST['price']);
        $description = trim($_POST['description']);
        $ingredients = trim($_POST['ingredients']);
        
        $image = $_FILES['image']['name'];
        $target_file = $target_dir . time() . '_' . basename($image);
        
        if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
            $sql = "INSERT INTO products (product_name, price, description, ingredients, image) VALUES (?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sdsss", $product_name, $price, $description, $ingredients, $target_file);
            
            if ($stmt->execute()) {
                $message = "Product added successfully!";
            } else {
                $error = "Error adding product: " . $conn->error;
            }
            $stmt->close();
        } else {
            $error = "Failed to upload image";
        }
    }
    
    // Handle Delete
    if ($_POST['action'] === 'delete' && isset($_POST['id'])) {
        $id = intval($_POST['id']);
        $sql = "DELETE FROM products WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            $message = "Product deleted successfully!";
        } else {
            $error = "Error deleting product: " . $conn->error;
        }
        $stmt->close();
    }
}

// Fetch all products
$products = $conn->query("SELECT * FROM products ORDER BY id DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Products - Ice Cream Shop</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Arial', sans-serif;
            background: #f5f5f5;
        }
        .container {
            display: flex;
            min-height: 100vh;
        }
        .sidebar {
            width: 280px;
            background: #2c3e50;
            color: white;
            padding: 30px 0;
        }
        .sidebar img {
            display: block;
            margin: 0 auto 30px;
            width: 150px;
        }
        .sidebar ul { list-style: none; }
        .sidebar a {
            display: block;
            padding: 12px 25px;
            color: #ecf0f1;
            text-decoration: none;
            transition: 0.3s;
        }
        .sidebar a:hover {
            background: #34495e;
            padding-left: 35px;
        }
        .main-content {
            flex: 1;
            padding: 30px;
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }
        .header h1 {
            color: #2c3e50;
            font-size: 28px;
        }
        .back-btn {
            padding: 10px 20px;
            background: #ec407a;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            transition: 0.3s;
        }
        .back-btn:hover { background: #d81b60; }
        .message {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
        }
        .success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .add-form {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
        .add-form h2 {
            color: #ec407a;
            margin-bottom: 20px;
        }
        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            color: #333;
            font-weight: bold;
        }
        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 10px;
            border: 2px solid #ffcdd2;
            border-radius: 5px;
            font-size: 14px;
        }
        .form-group textarea {
            height: 100px;
            resize: vertical;
        }
        .form-group input:focus,
        .form-group textarea:focus {
            border-color: #ec407a;
            outline: none;
        }
        .btn-submit {
            padding: 12px 30px;
            background: #ec407a;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            transition: 0.3s;
        }
        .btn-submit:hover { background: #d81b60; }
        .products-table {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th {
            background: #ec407a;
            color: white;
            padding: 12px;
            text-align: left;
        }
        td {
            padding: 12px;
            border-bottom: 1px solid #ffcdd2;
        }
        tr:hover { background: #f8f9fa; }
        .product-img {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 5px;
        }
        .action-btn {
            padding: 5px 10px;
            margin: 0 5px;
            border: none;
            border-radius: 3px;
            cursor: pointer;
            font-size: 12px;
            transition: 0.3s;
        }
        .edit-btn {
            background: #3498db;
            color: white;
        }
        .delete-btn {
            background: #e74c3c;
            color: white;
        }
        .action-btn:hover { opacity: 0.8; }
    </style>
</head>
<body>
    <div class="container">
        <div class="sidebar">
            <img src="../image/logo.png" alt="Logo">
            <ul>
                <li><a href="admin_dashboard.php">📊 Dashboard</a></li>
                <li><a href="manage_products.php">📦 Products</a></li>
                <li><a href="view_orders.php">📋 Orders</a></li>
                <li><a href="report.php">📈 Reports</a></li>
                <li><a href="logout.php">🚪 Logout</a></li>
            </ul>
        </div>
        <div class="main-content">
            <div class="header">
                <h1>Manage Products</h1>
                <a href="admin_dashboard.php" class="back-btn">← Back to Dashboard</a>
            </div>
            
            <?php if ($message): ?>
                <div class="message success"><?php echo $message; ?></div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="message error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <div class="add-form">
                <h2>Add New Product</h2>
                <form method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="add">
                    <div class="form-grid">
                        <div class="form-group">
                            <label>Product Name</label>
                            <input type="text" name="product_name" required>
                        </div>
                        <div class="form-group">
                            <label>Price (₹)</label>
                            <input type="number" name="price" step="0.01" required>
                        </div>
                        <div class="form-group">
                            <label>Description</label>
                            <textarea name="description" required></textarea>
                        </div>
                        <div class="form-group">
                            <label>Ingredients</label>
                            <textarea name="ingredients" required></textarea>
                        </div>
                        <div class="form-group">
                            <label>Product Image</label>
                            <input type="file" name="image" accept="image/*" required>
                        </div>
                    </div>
                    <button type="submit" class="btn-submit">Add Product</button>
                </form>
            </div>
            
            <div class="products-table">
                <h2 style="color: #ec407a; margin-bottom: 20px;">Product List</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Image</th>
                            <th>Name</th>
                            <th>Price</th>
                            <th>Description</th>
                            <th>Ingredients</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($row = $products->fetch_assoc()): ?>
                        <tr>
                            <td><img src="<?php echo htmlspecialchars($row['image']); ?>" class="product-img"></td>
                            <td><?php echo htmlspecialchars($row['product_name']); ?></td>
                            <td>₹<?php echo number_format($row['price'], 2); ?></td>
                            <td><?php echo substr(htmlspecialchars($row['description']), 0, 50) . '...'; ?></td>
                            <td><?php echo substr(htmlspecialchars($row['ingredients']), 0, 50) . '...'; ?></td>
                            <td>
                                <a href="edit_product.php?id=<?php echo $row['id']; ?>" class="action-btn edit-btn">Edit</a>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                                    <button type="submit" class="action-btn delete-btn" onclick="return confirm('Delete this product?')">Delete</button>
                                </form>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>