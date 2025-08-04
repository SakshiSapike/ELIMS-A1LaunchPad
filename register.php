<?php
// Database connection
$conn = new mysqli("localhost", "root", "root", "electronics_lab_inventory", 3307);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$message = "";
$message_class = "error";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $role = $_POST['role'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "❌ Invalid email format!";
    } elseif ($password !== $confirm_password) {
        $message = "❌ Passwords do not match!";
    } else {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $conn->prepare("INSERT INTO users (username, password_hash, email, role) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $username, $hashed_password, $email, $role);

        if ($stmt->execute()) {
            $message = "✅ Registered successfully. <a href='login.php'>Click here to login</a>.";
            $message_class = "success";
        } else {
            $message = "❌ Registration failed: " . $stmt->error;
        }

        $stmt->close();
    }
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register | ELIMS</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background-color: #0b1120;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }

        .register-container {
            background-color: #0f1c2e;
            padding: 40px 35px;
            border-radius: 12px;
            box-shadow: 0 0 18px #11f59f80;
            width: 400px;
        }

        .register-container h2 {
            text-align: center;
            color: #16e09f;
            font-size: 26px;
            margin-bottom: 25px;
        }

        label {
            color: #d6d6d6;
            font-weight: 500;
            display: block;
            margin-top: 12px;
            font-size: 14px;
        }

        input[type="text"],
        input[type="email"],
        input[type="password"],
        select {
            width: 100%;
            padding: 10px;
            margin-top: 6px;
            margin-bottom: 10px;
            border: none;
            border-radius: 6px;
            background-color: #1a273b;
            color: #ffffff;
            font-size: 14px;
        }

        input::placeholder {
            color: #aaa;
        }

        button {
            width: 100%;
            padding: 12px;
            margin-top: 20px;
            background-color: #16e09f;
            border: none;
            border-radius: 6px;
            color: #0b1120;
            font-weight: bold;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        button:hover {
            background-color: #14c38e;
        }

        .bottom-text {
            text-align: center;
            margin-top: 16px;
            font-size: 14px;
            color: #ccc;
        }

        .bottom-text a {
            color: #16e09f;
            font-weight: 600;
            text-decoration: none;
        }

        .bottom-text a:hover {
            text-decoration: underline;
        }

        .message.success {
            background-color: #0e2a1f;
            color: #16e09f;
            padding: 10px;
            border-radius: 6px;
            font-size: 14px;
            text-align: center;
            margin-top: 10px;
        }

        .message.error {
            background-color: #2d0e0e;
            color: #ff4f4f;
            padding: 10px;
            border-radius: 6px;
            font-size: 14px;
            text-align: center;
            margin-top: 10px;
        }
    </style>
</head>
<body>

<div class="register-container">
    <h2>⚡ Register at Electronics Lab Inventory</h2>

    <?php if ($message): ?>
        <div class="message <?= $message_class ?>"><?= htmlspecialchars_decode($message) ?></div>
    <?php endif; ?>

    <form method="post" action="">
        <label>Username</label>
        <input type="text" name="username" required placeholder="Enter your username">

        <label>Email</label>
        <input type="email" name="email" required placeholder="Enter your email">

        <label>Role</label>
        <select name="role" required>
            <option value="">-- Select Role --</option>
            <option value="admin">Admin</option>
            <option value="technician">Technician</option>
            <option value="researcher">Researcher</option>
        </select>

        <label>Password</label>
        <input type="password" name="password" required placeholder="Enter password">

        <label>Confirm Password</label>
        <input type="password" name="confirm_password" required placeholder="Re-enter password">

        <button type="submit">Create Account</button>
    </form>

    <div class="bottom-text">
        Already have an account? <a href="login.php">Login here</a>
    </div>
</div>

</body>
</html>
