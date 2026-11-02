<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['admin_username'])) {
    header("Location: admin_login.php");
    exit();
}

$message = '';
$error = '';

// Get product ID from URL
$product_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($product_id === 0) {
    header("Location: manage_products.php");
    exit();
}

// Fetch product details
$sql = "SELECT * FROM products WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();
$product = $result->fetch_assoc();

if (!$product) {
    header("Location: manage_products.php");
    exit();
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $product_name = trim($_POST['product_name']);
    $price = floatval($_POST['price']);
    $description = trim($_POST['description']);
    $ingredients = trim($_POST['ingredients']);
    
    $update_sql = "UPDATE products SET product_name = ?, price = ?, description = ?, ingredients = ?";
    $params = [$product_name, $price, $description, $ingredients];
    $types = "sdss";
    
    // Handle image upload if new image is provided
    if (!empty($_FILES['image']['name'])) {
        $target_dir = "../uploads/";
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        
        $image = time() . '_' . basename($_FILES['image']['name']);
        $target_file = $target_dir . $image;
        
        // Check if image file is actual image
        $check = getimagesize($_FILES['image']['tmp_name']);
        if ($check !== false) {
            if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
                $update_sql .= ", image = ?";
                $params[] = $target_file;
                $types .= "s";
                
                // Delete old image if exists
                if (!empty($product['image']) && file_exists($product['image'])) {
                    unlink($product['image']);
                }
            } else {
                $error = "Failed to upload image";
            }
        } else {
            $error = "File is not an image";
        }
    }
    
    $update_sql .= " WHERE id = ?";
    $params[] = $product_id;
    $types .= "i";
    
    if (empty($error)) {
        $stmt = $conn->prepare($update_sql);
        $stmt->bind_param($types, ...$params);
        
        if ($stmt->execute()) {
            $message = "Product updated successfully!";
            // Refresh product data
            $stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
            $stmt->bind_param("i", $product_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $product = $result->fetch_assoc();
        } else {
            $error = "Error updating product: " . $conn->error;
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Product - Ice Cream Shop</title>
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
            position: fixed;
            height: 100vh;
            overflow-y: auto;
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
            margin-left: 280px;
            padding: 30px;
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            background: white;
            padding: 20px 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
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
        .form-container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group.full-width {
            grid-column: span 2;
        }
        label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: bold;
        }
        input, textarea {
            width: 100%;
            padding: 12px;
            border: 2px solid #ffcdd2;
            border-radius: 8px;
            font-size: 14px;
            transition: 0.3s;
        }
        input:focus, textarea:focus {
            border-color: #ec407a;
            outline: none;
            box-shadow: 0 0 10px rgba(236,64,122,0.1);
        }
        textarea {
            height: 120px;
            resize: vertical;
        }
        .current-image {
            margin: 15px 0;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 8px;
        }
        .current-image img {
            max-width: 200px;
            max-height: 200px;
            border-radius: 8px;
            border: 2px solid #ffcdd2;
        }
        .image-hint {
            font-size: 12px;
            color: #7f8c8d;
            margin-top: 5px;
        }
        .btn-group {
            display: flex;
            gap: 15px;
            margin-top: 30px;
        }
        .btn-update {
            padding: 14px 30px;
            background: #ec407a;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: 0.3s;
        }
        .btn-update:hover {
            background: #d81b60;
            transform: scale(1.02);
        }
        .btn-cancel {
            padding: 14px 30px;
            background: #95a5a6;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: bold;
            text-align: center;
            transition: 0.3s;
        }
        .btn-cancel:hover {
            background: #7f8c8d;
        }
        .preview-title {
            color: #ec407a;
            margin: 20px 0 10px;
            font-size: 18px;
        }
        .info-box {
            background: #fff3e0;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 5px solid #ff9800;
        }
        @media (max-width: 768px) {
            .form-grid {
                grid-template-columns: 1fr;
            }
            .form-group.full-width {
                grid-column: span 1;
            }
            .main-content {
                margin-left: 0;
            }
            .sidebar {
                display: none;
            }
        }
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
                <h1>✏️ Edit Product: <?php echo htmlspecialchars($product['product_name']); ?></h1>
                <a href="manage_products.php" class="back-btn">← Back to Products</a>
            </div>
            
            <?php if ($message): ?>
                <div class="message success"><?php echo $message; ?></div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="message error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <div class="info-box">
                <strong>ℹ️ Info:</strong> Leave image field empty to keep current image. New image will replace the old one.
            </div>
            
            <div class="form-container">
                <form method="POST" enctype="multipart/form-data">
                    <div class="form-grid">
                        <div class="form-group">
                            <label>Product Name *</label>
                            <input type="text" name="product_name" value="<?php echo htmlspecialchars($product['product_name']); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label>Price (₹) *</label>
                            <input type="number" name="price" step="0.01" value="<?php echo htmlspecialchars($product['price']); ?>" required>
                        </div>
                        
                        <div class="form-group full-width">
                            <label>Description *</label>
                            <textarea name="description" required><?php echo htmlspecialchars($product['description']); ?></textarea>
                        </div>
                        
                        <div class="form-group full-width">
                            <label>Ingredients *</label>
                            <textarea name="ingredients" required><?php echo htmlspecialchars($product['ingredients']); ?></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label>Product Image</label>
                            <input type="file" name="image" accept="image/*">
                            <div class="image-hint">Allowed: JPG, JPEG, PNG, WEBP. Max size: 5MB</div>
                        </div>
                        
                        <div class="form-group">
                            <label>Current Image</label>
                            <div class="current-image">
                                <?php if (!empty($product['image']) && file_exists($product['image'])): ?>
                                    <img src="<?php echo htmlspecialchars($product['image']); ?>" 
                                         alt="<?php echo htmlspecialchars($product['product_name']); ?>">
                                <?php else: ?>
                                    <p style="color: #999;">No image available</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    
                    <div class="btn-group">
                        <button type="submit" class="btn-update">Update Product</button>
                        <a href="manage_products.php" class="btn-cancel">Cancel</a>
                    </div>
                </form>
            </div>
            
            <!-- Product Preview Section -->
            <div style="margin-top: 30px; background: white; padding: 30px; border-radius: 10px;">
                <h3 class="preview-title">👀 Live Preview</h3>
                <div style="display: flex; gap: 30px; align-items: center; flex-wrap: wrap;">
                    <div style="flex: 1; min-width: 200px;">
                        <?php if (!empty($product['image']) && file_exists($product['image'])): ?>
                            <img src="<?php echo htmlspecialchars($product['image']); ?>" 
                                 alt="<?php echo htmlspecialchars($product['product_name']); ?>"
                                 style="width: 100%; max-width: 300px; border-radius: 10px; box-shadow: 0 5px 15px rgba(0,0,0,0.1);">
                        <?php endif; ?>
                    </div>
                    <div style="flex: 2;">
                        <h2 style="color: #ec407a; margin-bottom: 10px;"><?php echo htmlspecialchars($product['product_name']); ?></h2>
                        <p style="color: #666; margin-bottom: 15px; line-height: 1.6;"><?php echo htmlspecialchars($product['description']); ?></p>
                        <p style="color: #27ae60; font-size: 24px; font-weight: bold; margin-bottom: 10px;">₹<?php echo number_format($product['price'], 2); ?></p>
                        <p style="color: #7f8c8d; font-size: 14px;"><strong>Ingredients:</strong> <?php echo htmlspecialchars($product['ingredients']); ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        // Preview image before upload
        document.querySelector('input[type="file"]').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const preview = document.querySelector('.current-image img');
                    if (preview) {
                        preview.src = e.target.result;
                    } else {
                        const currentImageDiv = document.querySelector('.current-image');
                        currentImageDiv.innerHTML = `<img src="${e.target.result}" style="max-width: 200px; max-height: 200px; border-radius: 8px;">`;
                    }
                }
                reader.readAsDataURL(file);
            }
        });
        
        // Form validation
        document.querySelector('form').addEventListener('submit', function(e) {
            const price = document.querySelector('input[name="price"]').value;
            if (price <= 0) {
                e.preventDefault();
                alert('Price must be greater than 0');
            }
        });
    </script>
</body>
</html>