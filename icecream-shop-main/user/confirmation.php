<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Confirmation - Ice Cream Shop</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Arial', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .confirmation-box {
            background: white;
            padding: 50px;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.2);
            text-align: center;
            max-width: 500px;
            animation: slideIn 0.5s ease-out;
        }
        @keyframes slideIn {
            from {
                transform: translateY(-50px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }
        .success-icon {
            font-size: 80px;
            color: #4CAF50;
            margin-bottom: 20px;
        }
        h1 {
            color: #2c3e50;
            margin-bottom: 20px;
            font-size: 32px;
        }
        p {
            color: #7f8c8d;
            margin-bottom: 15px;
            font-size: 18px;
            line-height: 1.6;
        }
        .order-number {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 10px;
            margin: 25px 0;
            border: 2px dashed #ec407a;
        }
        .order-number span {
            color: #ec407a;
            font-weight: bold;
            font-size: 24px;
        }
        .btn {
            display: inline-block;
            padding: 15px 40px;
            background: #ec407a;
            color: white;
            text-decoration: none;
            border-radius: 50px;
            font-weight: bold;
            font-size: 18px;
            transition: 0.3s;
            margin-top: 20px;
        }
        .btn:hover {
            background: #d81b60;
            transform: scale(1.05);
        }
        .delivery-info {
            margin: 20px 0;
            padding: 15px;
            background: #fff3e0;
            border-radius: 10px;
            color: #e65100;
        }
    </style>
</head>
<body>
    <div class="confirmation-box">
        <div class="success-icon">✅</div>
        <h1>Thank You for Your Order!</h1>
        <p>Your ice cream order has been successfully placed.</p>
        
        <div class="order-number">
            <p>Order Number</p>
            <span>#IC<?php echo date('Ymd') . rand(1000,9999); ?></span>
        </div>
        
        <div class="delivery-info">
            🚚 Estimated delivery: 30-45 minutes
        </div>
        
        <p style="font-size: 16px;">
            A confirmation has been sent to your email.<br>
            Get ready for some delicious ice cream! 🍦
        </p>
        
        <a href="menu.php" class="btn">Continue Shopping</a>
    </div>
</body>
</html>