<?php
session_start();
$conn = new mysqli("localhost", "root", "root", "electronics_lab_inventory", 3307);
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

// Fetch notifications
$result = $conn->query("SELECT n.id, n.message, n.type, n.is_read, n.created_at, c.name AS component_name
                        FROM notifications n
                        JOIN components c ON n.component_id = c.id
                        ORDER BY n.created_at DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>🔔 Notifications - ELIMS</title>
    <style>
        body {
            background-color: #0e1c30;
            color: white;
            font-family: 'Segoe UI', sans-serif;
            padding: 20px;
        }

        h2 {
            color: #16e09f;
        }

        .notification {
            padding: 15px;
            margin: 15px 0;
            border-radius: 10px;
            background: #102647;
            box-shadow: 0 0 10px rgba(22, 224, 159, 0.3);
        }

        .low_stock {
            border-left: 5px solid #f39c12;
        }

        .old_stock {
            border-left: 5px solid #e74c3c;
        }

        .unread {
            background-color: #1f2f4a;
        }

        .component {
            font-weight: bold;
            color: #1abc9c;
        }

        .type-label {
            padding: 4px 8px;
            border-radius: 5px;
            font-size: 0.85em;
            font-weight: bold;
            display: inline-block;
            margin-bottom: 5px;
        }

        .low_stock .type-label {
            background-color: #f1c40f;
            color: #1b1b1b;
        }

        .old_stock .type-label {
            background-color: #c0392b;
            color: white;
        }

        .timestamp {
            font-size: 0.8em;
            color: #ccc;
            margin-top: 5px;
        }

        .empty {
            margin-top: 30px;
            text-align: center;
            color: #888;
            font-style: italic;
        }
    </style>
</head>
<body>

<h2>🔔 Notifications</h2>

<?php if ($result->num_rows > 0): ?>
    <?php while ($row = $result->fetch_assoc()): ?>
        <div class="notification <?= $row['type'] ?> <?= !$row['is_read'] ? 'unread' : '' ?>">
            <div class="type-label"><?= strtoupper(str_replace('_', ' ', $row['type'])) ?></div>
            <div>
                <span class="component">🔧 <?= htmlspecialchars($row['component_name']) ?>:</span>
                <?= htmlspecialchars($row['message']) ?>
            </div>
            <div class="timestamp">🕒 <?= $row['created_at'] ?></div>
        </div>
    <?php endwhile; ?>
<?php else: ?>
    <div class="empty">No notifications available.</div>
<?php endif; ?>
<p style="margin-top: 20px;">
    <a href="inventory.php" style="color:#16e09f;">⬅ Back to Inventory</a>
</p>
</body>
</html>

<?php $conn->close(); ?>
