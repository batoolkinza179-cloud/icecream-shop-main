<?php
session_start();
require_once '../admin/config.php';

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

if (empty($_SESSION['cart'])) {
    header("Location: menu.php");
    exit();
}

$error = '';
$success = '';

// Process order
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['name']);
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);
    
    if (empty($name) || empty($phone) || empty($address)) {
        $error = "All fields are required";
    } else {
        $conn->begin_transaction();
        
        try {
            $order_date = date('Y-m-d');
            $total_price = 0;
            
            // Calculate total
            foreach ($_SESSION['cart'] as $item_id => $quantity) {
                $sql = "SELECT price FROM products WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $item_id);
                $stmt->execute();
                $result = $stmt->get_result();
                $item = $result->fetch_assoc();
                $total_price += $item['price'] * $quantity;
            }
            
            // Insert orders
            foreach ($_SESSION['cart'] as $item_id => $quantity) {
                $sql = "SELECT product_name, price FROM products WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $item_id);
                $stmt->execute();
                $result = $stmt->get_result();
                $item = $result->fetch_assoc();
                
                $insert = "INSERT INTO orders (name, phone, address, total_price, order_date, product_name, quantity, price) 
                          VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($insert);
                $stmt->bind_param("sssdssii", $name, $phone, $address, $total_price, $order_date, 
                                $item['product_name'], $quantity, $item['price']);
                $stmt->execute();
            }
            
            $conn->commit();
            unset($_SESSION['cart']);
            header("Location: confirmation.php");
            exit();
            
        } catch (Exception $e) {
            $conn->rollback();
            $error = "Order failed: " . $e->getMessage();
        }
    }
}

// Get cart details for display
$cart_items = [];
$total_amount = 0;

if (!empty($_SESSION['cart'])) {
    $ids = implode(',', array_keys($_SESSION['cart']));
    $sql = "SELECT * FROM products WHERE id IN ($ids)";
    $result = $conn->query($sql);
    
    while ($row = $result->fetch_assoc()) {
        $row['cart_quantity'] = $_SESSION['cart'][$row['id']];
        $row['subtotal'] = $row['price'] * $row['cart_quantity'];
        $total_amount += $row['subtotal'];
        $cart_items[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - Ice Cream Shop</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Arial', sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding: 20px;
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .header h1 {
            color: #ec407a;
            font-size: 32px;
        }
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            font-size: 14px;
            transition: 0.3s;
        }
        .btn-secondary {
            background: #3498db;
            color: white;
        }
        .btn-secondary:hover { background: #2980b9; }
        .checkout-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
        }
        .order-summary, .delivery-form {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        h2 {
            color: #ec407a;
            margin-bottom: 20px;
        }
        .cart-items {
            margin-bottom: 20px;
        }
        .cart-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 0;
            border-bottom: 1px solid #ffcdd2;
        }
        .item-name {
            font-weight: bold;
            color: #2c3e50;
        }
        .item-quantity {
            color: #7f8c8d;
        }
        .item-price {
            color: #27ae60;
            font-weight: bold;
        }
        .total-row {
            display: flex;
            justify-content: space-between;
            padding: 15px 0;
            font-size: 18px;
            border-top: 2px solid #ec407a;
        }
        .grand-total {
            color: #ec407a;
            font-weight: bold;
            font-size: 24px;
        }
        .form-group {
            margin-bottom: 20px;
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
        }
        textarea {
            height: 100px;
            resize: vertical;
        }
        .btn-submit {
            width: 100%;
            padding: 15px;
            background: #ec407a;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 18px;
            font-weight: bold;
            cursor: pointer;
            transition: 0.3s;
        }
        .btn-submit:hover { background: #d81b60; }
        .error {
            background: #f8d7da;
            color: #721c24;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
        }
        .delivery-fee {
            color: #e67e22;
            font-size: 14px;
            margin-top: 5px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📦 Checkout</h1>
            <a href="cart.php" class="btn btn-secondary">← Back to Cart</a>
        </div>
        
        <?php if ($error): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <div class="checkout-grid">
            <div class="order-summary">
                <h2>Order Summary</h2>
                <div class="cart-items">
                    <?php foreach ($cart_items as $item): ?>
                    <div class="cart-item">
                        <div>
                            <span class="item-name"><?php echo htmlspecialchars($item['product_name']); ?></span>
                            <span class="item-quantity"> (x<?php echo $item['cart_quantity']; ?>)</span>
                        </div>
                        <span class="item-price">₹<?php echo number_format($item['subtotal'], 2); ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>
                
                <div class="total-row">
                    <span>Subtotal:</span>
                    <span>₹<?php echo number_format($total_amount, 2); ?></span>
                </div>
                <div class="total-row">
                    <span>Delivery Fee:</span>
                    <span>₹40.00</span>
                </div>
                <div class="total-row grand-total">
                    <span>Total:</span>
                    <span>₹<?php echo number_format($total_amount + 40, 2); ?></span>
                </div>
                <p class="delivery-fee">*Free delivery on orders above ₹500</p>
            </div>
            
            <div class="delivery-form">
                <h2>Delivery Details</h2>
                <form method="POST">
                    <div class="form-group">
                        <label>Full Name</label>
                        <input type="text" name="name" value="<?php echo htmlspecialchars($_SESSION['username']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Phone Number</label>
                        <input type="text" name="phone" placeholder="10-digit mobile number" required>
                    </div>
                    <div class="form-group">
                        <label>Delivery Address</label>
                        <textarea name="address" placeholder="House No, Street, City, PIN" required></textarea>
                    </div>
                    <button type="submit" class="btn-submit">Place Order</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>