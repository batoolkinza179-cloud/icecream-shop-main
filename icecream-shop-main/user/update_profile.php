<?php
session_start();
require_once '../admin/config.php';

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

$message = '';
$error = '';

// Get current user data
$sql = "SELECT * FROM users WHERE username = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $_SESSION['username']);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

// Handle profile update
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $new_username = trim($_POST['username']);
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Verify current password (plain text comparison)
    if ($current_password !== $user['password']) {
        $error = "Current password is incorrect";
    } else {
        // Check if username is taken (if changed)
        if ($new_username !== $user['username']) {
            $check_sql = "SELECT id FROM users WHERE username = ? AND id != ?";
            $check_stmt = $conn->prepare($check_sql);
            $check_stmt->bind_param("si", $new_username, $user['id']);
            $check_stmt->execute();
            if ($check_stmt->get_result()->num_rows > 0) {
                $error = "Username already taken";
            }
            $check_stmt->close();
        }
        
        if (empty($error)) {
            // Update password if provided
            if (!empty($new_password)) {
                if (strlen($new_password) < 3) {
                    $error = "New password must be at least 3 characters";
                } elseif ($new_password !== $confirm_password) {
                    $error = "New passwords do not match";
                } else {
                    $update_sql = "UPDATE users SET username = ?, password = ? WHERE id = ?";
                    $update_stmt = $conn->prepare($update_sql);
                    $update_stmt->bind_param("ssi", $new_username, $new_password, $user['id']);
                }
            } else {
                // Update only username
                $update_sql = "UPDATE users SET username = ? WHERE id = ?";
                $update_stmt = $conn->prepare($update_sql);
                $update_stmt->bind_param("si", $new_username, $user['id']);
            }
            
            if (empty($error) && isset($update_stmt) && $update_stmt->execute()) {
                $_SESSION['username'] = $new_username;
                $_SESSION['update_success'] = "Profile updated successfully!";
                
                // FIXED: Redirect to dashboard with success message
                header("Location: user_dashboard.php?update=success");
                exit();
            } elseif (empty($error)) {
                $error = "Update failed: " . $conn->error;
            }
            if (isset($update_stmt)) $update_stmt->close();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Profile - Ice Cream Shop</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            font-family: 'Arial', sans-serif;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .profile-container {
            background: white;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            width: 100%;
            max-width: 500px;
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
        .btn-group {
            display: flex;
            gap: 15px;
            margin-top: 20px;
        }
        .btn-update {
            flex: 1;
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
        .btn-update:hover { background: #d81b60; }
        .btn-cancel {
            flex: 1;
            padding: 14px;
            background: #95a5a6;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            text-decoration: none;
            text-align: center;
            transition: 0.3s;
        }
        .btn-cancel:hover { background: #7f8c8d; }
        .message {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
            text-align: center;
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
        .info-box {
            background: #e3f2fd;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            color: #1565c0;
            font-size: 14px;
        }
        hr {
            margin: 20px 0;
            border: none;
            border-top: 2px solid #ffcdd2;
        }
        .password-hint {
            font-size: 12px;
            color: #7f8c8d;
            margin-top: 5px;
        }
    </style>
</head>
<body>
    <div class="profile-container">
        <h1>👤 Update Profile</h1>
        
        <?php if ($message): ?>
            <div class="message success"><?php echo $message; ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="message error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <div class="info-box">
            <strong>📧 Email:</strong> <?php echo htmlspecialchars($user['email']); ?><br>
            <small>Email cannot be changed</small>
        </div>
        
        <form method="POST">
            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required>
            </div>
            
            <hr>
            
            <div class="form-group">
                <label>Current Password</label>
                <input type="password" name="current_password" required>
            </div>
            <div class="form-group">
                <label>New Password (leave blank to keep current)</label>
                <input type="password" name="new_password">
                <div class="password-hint">Minimum 3 characters</div>
            </div>
            <div class="form-group">
                <label>Confirm New Password</label>
                <input type="password" name="confirm_password">
            </div>
            
            <div class="btn-group">
                <button type="submit" class="btn-update">Update Profile</button>
                <a href="user_dashboard.php" class="btn-cancel">Cancel</a>
            </div>
        </form>
    </div>
</body>
</html>