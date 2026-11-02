<?php
session_start();
require_once '../admin/config.php';

$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    
    // Direct check with plain text password
    $sql = "SELECT * FROM users WHERE username = ? AND password = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $username, $password);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        $_SESSION['id'] = $row['id'];
        $_SESSION['username'] = $row['username'];
        $_SESSION['email'] = $row['email'];
        header("Location: user_dashboard.php");
        exit();
    } else {
        $error = "Invalid username or password";
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Login - Ice Cream Shop</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            font-family: 'Arial', sans-serif;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            position: relative;
        }
        .back-home-btn {
            position: absolute;
            top: 20px;
            left: 20px;
            padding: 12px 25px;
            background: rgba(255,255,255,0.2);
            color: white;
            text-decoration: none;
            border-radius: 50px;
            font-weight: bold;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: 0.3s;
            border: 2px solid white;
            backdrop-filter: blur(5px);
            z-index: 1000;
        }
        .back-home-btn:hover {
            background: white;
            color: #ec407a;
            transform: translateX(-5px);
        }
        .back-home-btn span {
            font-size: 20px;
        }
        .login-container {
            background: white;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            width: 100%;
            max-width: 400px;
            position: relative;
        }
        h1 {
            color: #ec407a;
            text-align: center;
            margin-bottom: 30px;
            font-size: 32px;
        }
        .form-group { margin-bottom: 20px; }
        label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: bold;
        }
        input {
            width: 100%;
            padding: 12px;
            border: 2px solid #ffcdd2;
            border-radius: 8px;
            font-size: 14px;
            transition: 0.3s;
        }
        input:focus {
            border-color: #ec407a;
            outline: none;
        }
        button {
            width: 100%;
            padding: 14px;
            background: #ec407a;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: 0.3s;
        }
        button:hover { background: #d81b60; }
        .error {
            color: #f44336;
            text-align: center;
            margin-bottom: 15px;
            padding: 10px;
            background: #ffebee;
            border-radius: 5px;
        }
        .register-link {
            text-align: center;
            margin-top: 20px;
        }
        .register-link a {
            color: #ec407a;
            text-decoration: none;
            font-weight: bold;
        }
        .register-link a:hover { text-decoration: underline; }
        .demo-credentials {
            margin-top: 20px;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 8px;
            font-size: 14px;
            border-left: 4px solid #ec407a;
        }
        .demo-credentials p {
            margin: 5px 0;
            color: #333;
        }
    </style>
</head>
<body>
    <!-- Back to Home Button -->
    <a href="../home/index.php" class="back-home-btn">
        <span>←</span> Back to Home
    </a>

    <div class="login-container">
        <h1>🍦 User Login</h1>
        
        <?php if ($error): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" required>
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" required>
            </div>
            <button type="submit">Login</button>
        </form>
        
        <div class="register-link">
            Don't have an account? <a href="register.php">Register here</a>
        </div>
        
        
    </div>
</body>
</html>