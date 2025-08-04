<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

$username = $_SESSION['username'];
$role = $_SESSION['role'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard | ELIMS</title>
    <style>
        body {
            margin: 0;
            font-family: 'Segoe UI', sans-serif;
            background-color: #0b1120;
            color: #fff;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 40px 20px;
        }

        .dashboard-container {
            background-color: #0f1c2e;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 0 20px #11f59f80;
            max-width: 600px;
            width: 100%;
            text-align: center;
        }

        .dashboard-container h2 {
            color: #16e09f;
            margin-bottom: 10px;
        }

        .dashboard-container p {
            font-size: 16px;
            margin-bottom: 20px;
        }

        .logout-btn {
            background-color: #16e09f;
            border: none;
            color: #0b1120;
            padding: 12px 24px;
            font-weight: bold;
            border-radius: 6px;
            cursor: pointer;
            font-size: 15px;
            transition: background-color 0.3s ease;
            margin-top: 20px;
        }

        .logout-btn:hover {
            background-color: #14c38e;
        }

        .quick-links {
            margin-top: 25px;
        }

        .quick-links a {
            display: block;
            margin: 10px 0;
            color: #1dc997;
            text-decoration: none;
            font-weight: bold;
            font-size: 15px;
        }

        .quick-links a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

<div class="dashboard-container">
    <h2>⚡ Welcome, <?= htmlspecialchars($username) ?>!</h2>
    <p>Your role: <strong><?= htmlspecialchars(ucfirst($role)) ?></strong></p>

    <p>You have successfully logged in to the <strong>Electronics Lab Inventory Management System</strong>.</p>

    <div class="quick-links">
        <a href="inventory.php?filter=low_stock">📉 View All Low Stock Items</a>
        <a href="notifications.php?type=old_stock">📦 View Old Stock Alerts</a>
    </div>

    <form method="post" action="logout.php">
        <button type="submit" class="logout-btn">Logout</button>
    </form>
</div>

</body>
</html>
