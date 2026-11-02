<?php
session_start();
require_once '../admin/config.php';

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

// Handle cart actions
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['clear_cart'])) {
        unset($_SESSION['cart']);
        header("Location: cart.php");
        exit();
    }
    
    if (isset($_POST['remove_item'])) {
        $item_id = $_POST['item_id'];
        if (isset($_SESSION['cart'][$item_id])) {
            unset($_SESSION['cart'][$item_id]);
        }
        header("Location: cart.php");
        exit();
    }
    
    if (isset($_POST['update_quantity'])) {
        $item_id = $_POST['item_id'];
        $quantity = intval($_POST['quantity']);
        if ($quantity > 0) {
            $_SESSION['cart'][$item_id] = $quantity;
        } else {
            unset($_SESSION['cart'][$item_id]);
        }
        header("Location: cart.php");
        exit();
    }
}

$cart_items = $_SESSION['cart'] ?? [];
$total_amount = 0;
$cart_details = [];

if (!empty($cart_items)) {
    $ids = implode(',', array_keys($cart_items));
    $sql = "SELECT * FROM products WHERE id IN ($ids)";
    $result = $conn->query($sql);
    
    while ($row = $result->fetch_assoc()) {
        $row['cart_quantity'] = $cart_items[$row['id']];
        $row['subtotal'] = $row['price'] * $row['cart_quantity'];
        $total_amount += $row['subtotal'];
        $cart_details[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart - Ice Cream Shop</title>
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
        .btn-primary {
            background: #ec407a;
            color: white;
        }
        .btn-primary:hover { background: #d81b60; }
        .btn-secondary {
            background: #3498db;
            color: white;
        }
        .btn-secondary:hover { background: #2980b9; }
        .btn-danger {
            background: #e74c3c;
            color: white;
        }
        .btn-danger:hover { background: #c0392b; }
        .empty-cart {
            text-align: center;
            padding: 60px;
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .empty-cart h2 {
            color: #2c3e50;
            margin-bottom: 20px;
        }
        .cart-items {
            background: white;
            border-radius: 15px;
            padding: 20px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .cart-item {
            display: flex;
            align-items: center;
            padding: 20px;
            border-bottom: 1px solid #ffcdd2;
        }
        .cart-item:last-child { border-bottom: none; }
        .item-image {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 10px;
            margin-right: 20px;
        }
        .item-details {
            flex: 1;
        }
        .item-title {
            color: #2c3e50;
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .item-price {
            color: #ec407a;
            font-size: 16px;
            margin-bottom: 10px;
        }
        .quantity-form {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .quantity-input {
            width: 60px;
            padding: 5px;
            border: 2px solid #ffcdd2;
            border-radius: 5px;
        }
        .item-subtotal {
            font-size: 18px;
            font-weight: bold;
            color: #27ae60;
            margin: 0 20px;
        }
        .item-actions {
            display: flex;
            gap: 10px;
        }
        .cart-summary {
            margin-top: 30px;
            padding: 20px;
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .summary-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 0;
            border-bottom: 1px solid #ffcdd2;
        }
        .summary-row.total {
            border-bottom: none;
            font-size: 20px;
            font-weight: bold;
            color: #ec407a;
        }
        .cart-actions {
            display: flex;
            justify-content: space-between;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🛒 Your Shopping Cart</h1>
            <a href="menu.php" class="btn btn-secondary">← Continue Shopping</a>
        </div>
        
        <?php if (empty($cart_details)): ?>
            <div class="empty-cart">
                <h2>Your cart is empty</h2>
                <p style="margin-bottom: 20px; color: #7f8c8d;">Looks like you haven't added any items yet.</p>
                <a href="menu.php" class="btn btn-primary">Browse Menu</a>
            </div>
        <?php else: ?>
            <div class="cart-items">
                <?php foreach ($cart_details as $item): ?>
                <div class="cart-item">
                    <img src="<?php echo htmlspecialchars($item['image']); ?>" 
                         alt="<?php echo htmlspecialchars($item['product_name']); ?>" 
                         class="item-image">
                    <div class="item-details">
                        <div class="item-title"><?php echo htmlspecialchars($item['product_name']); ?></div>
                        <div class="item-price">₹<?php echo number_format($item['price'], 2); ?> each</div>
                    </div>
                    
                    <form method="POST" class="quantity-form">
                        <input type="hidden" name="item_id" value="<?php echo $item['id']; ?>">
                        <input type="hidden" name="update_quantity" value="1">
                        <input type="number" name="quantity" value="<?php echo $item['cart_quantity']; ?>" 
                               min="1" class="quantity-input" onchange="this.form.submit()">
                    </form>
                    
                    <div class="item-subtotal">
                        ₹<?php echo number_format($item['subtotal'], 2); ?>
                    </div>
                    
                    <div class="item-actions">
                        <form method="POST">
                            <input type="hidden" name="item_id" value="<?php echo $item['id']; ?>">
                            <input type="hidden" name="remove_item" value="1">
                            <button type="submit" class="btn btn-danger btn-small">Remove</button>
                        </form>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            
            <div class="cart-summary">
                <div class="summary-row">
                    <span>Subtotal:</span>
                    <span>₹<?php echo number_format($total_amount, 2); ?></span>
                </div>
                <div class="summary-row">
                    <span>Delivery Fee:</span>
                    <span>₹40.00</span>
                </div>
                <div class="summary-row total">
                    <span>Total:</span>
                    <span>₹<?php echo number_format($total_amount + 40, 2); ?></span>
                </div>
                
                <div class="cart-actions">
                    <form method="POST">
                        <input type="hidden" name="clear_cart" value="1">
                        <button type="submit" class="btn btn-danger">Clear Cart</button>
                    </form>
                    <a href="order.php" class="btn btn-primary">Proceed to Checkout →</a>
                </div>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>