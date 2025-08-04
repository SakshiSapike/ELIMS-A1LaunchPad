<?php
session_start();

// Handle login logic
$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $conn = new mysqli("localhost", "root", "root", "electronics_lab_inventory", 3307);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $row = $result->fetch_assoc();

        if (password_verify($password, $row['password_hash'])) {
            $_SESSION['username'] = $username;
            $_SESSION['role'] = $row['role'];

            // Redirect based on role
            if ($_SESSION['role'] == 'Admin') {
                header("Location: dashboard.php");
            } else {
                header("Location: inventory.php");
            }
            exit();
        } else {
            $message = "❌ Incorrect password.";
        }
    } else {
        $message = "❌ User not found.";
    }

    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Electronics Lab Inventory Login</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #0b1d3a;
            margin: 0;
            padding: 0;
            color: white;
        }

        .container {
            width: 400px;
            margin: 80px auto;
            background: #102647;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 15px #1dc997;
        }

        h2 {
            text-align: center;
            margin-bottom: 20px;
            color: #1dc997;
        }

        label {
            display: block;
            margin-top: 15px;
            margin-bottom: 5px;
            color: #c2e5dd;
        }

        input[type="text"], input[type="password"] {
            width: 100%;
            padding: 10px;
            border: none;
            border-radius: 6px;
            background: #1c2e4a;
            color: white;
        }

        input[type="submit"] {
            width: 100%;
            margin-top: 20px;
            padding: 10px;
            background-color: #1dc997;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            color: #102647;
            font-weight: bold;
            cursor: pointer;
        }

        input[type="submit"]:hover {
            background-color: #17b989;
        }

        .footer {
            text-align: center;
            margin-top: 20px;
            font-size: 14px;
            color: #aaa;
        }

        .footer a {
            color: #1dc997;
            text-decoration: none;
            font-weight: bold;
        }

        .footer a:hover {
            text-decoration: underline;
        }

        .logo {
            text-align: center;
            font-size: 28px;
            font-weight: bold;
            margin-bottom: 20px;
            color: #ffffff;
        }

        .logo span {
            color: #1dc997;
        }

        .message {
            text-align: center;
            margin-bottom: 15px;
            color: red;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="logo">⚡ <span>Electronics</span> Lab Inventory</div>
        <h2>Login</h2>

        <?php if (!empty($message)): ?>
            <div class="message"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <label for="username">Username:</label>
            <input type="text" id="username" name="username" required>

            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required>

            <input type="submit" value="Login">
        </form>

        <div class="footer">
            Don’t have an account? <a href="register.php">Register</a>
        </div>
    </div>
</body>
</html>
