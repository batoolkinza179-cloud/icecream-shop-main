<?php
session_start();
require_once '../admin/config.php';

$error = '';
$success = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Validation
    if (empty($username) || empty($email) || empty($password)) {
        $error = "All fields are required";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format";
    } elseif (strlen($password) < 3) {
        $error = "Password must be at least 3 characters";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match";
    } else {
        // Check if user exists
        $check_sql = "SELECT id FROM users WHERE username = ? OR email = ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("ss", $username, $email);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        
        if ($check_result->num_rows > 0) {
            $error = "Username or email already exists";
        } else {
            // Insert new user with plain text password
            $insert_sql = "INSERT INTO users (username, email, password) VALUES (?, ?, ?)";
            $insert_stmt = $conn->prepare($insert_sql);
            $insert_stmt->bind_param("sss", $username, $email, $password);
            
            if ($insert_stmt->execute()) {
                $_SESSION['id'] = $insert_stmt->insert_id;
                $_SESSION['username'] = $username;
                $_SESSION['email'] = $email;
                header("Location: user_dashboard.php");
                exit();
            } else {
                $error = "Registration failed: " . $conn->error;
            }
            $insert_stmt->close();
        }
        $check_stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Ice Cream Shop</title>
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
        .register-container {
            background: white;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            width: 100%;
            max-width: 450px;
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
        .password-hint {
            font-size: 12px;
            color: #7f8c8d;
            margin-top: 5px;
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
        .success {
            color: #4CAF50;
            text-align: center;
            margin-bottom: 15px;
            padding: 10px;
            background: #d4edda;
            border-radius: 5px;
        }
        .login-link {
            text-align: center;
            margin-top: 20px;
        }
        .login-link a {
            color: #ec407a;
            text-decoration: none;
            font-weight: bold;
        }
        .login-link a:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <!-- Back to Home Button -->
    <a href="../home/index.php" class="back-home-btn">
        <span>←</span> Back to Home
    </a>

    <div class="register-container">
        <h1>🍦 Create Account</h1>
        
        <?php if ($error): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>" required>
            </div>
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required>
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" required>
                <div class="password-hint">Minimum 3 characters</div>
            </div>
            <div class="form-group">
                <label>Confirm Password</label>
                <input type="password" name="confirm_password" required>
            </div>
            <button type="submit">Register</button>
        </form>
        
        <div class="login-link">
            Already have an account? <a href="login.php">Login here</a>
        </div>
    </div>
</body>
</html>