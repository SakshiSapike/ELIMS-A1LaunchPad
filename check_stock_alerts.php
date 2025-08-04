<?php
$conn = new mysqli("localhost", "root", "root", "electronics_lab_inventory", 3307);
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

// Threshold for old stock (e.g., 180 days)
$old_stock_days = 180;

// 🔍 1. LOW STOCK ALERTS
$low_stock_q = "SELECT id, name FROM components WHERE quantity < threshold";
$low_result = $conn->query($low_stock_q);

while ($row = $low_result->fetch_assoc()) {
    $cid = $row['id'];
    $message = "Low stock warning for component '{$row['name']}'.";

    // Check if notification already exists (avoid duplicates)
    $check = $conn->query("SELECT id FROM notifications WHERE component_id = $cid AND type = 'low_stock'");
    if ($check->num_rows == 0) {
        $stmt = $conn->prepare("INSERT INTO notifications (component_id, type, message) VALUES (?, 'low_stock', ?)");
        $stmt->bind_param("is", $cid, $message);
        $stmt->execute();
    }
}

// 🔍 2. OLD STOCK ALERTS
$old_stock_q = "SELECT id, name FROM components WHERE added_at < NOW() - INTERVAL $old_stock_days DAY";
$old_result = $conn->query($old_stock_q);

while ($row = $old_result->fetch_assoc()) {
    $cid = $row['id'];
    $message = "Old stock alert: Component '{$row['name']}' hasn't been used in over $old_stock_days days.";

    // Check if already alerted
    $check = $conn->query("SELECT id FROM notifications WHERE component_id = $cid AND type = 'old_stock'");
    if ($check->num_rows == 0) {
        $stmt = $conn->prepare("INSERT INTO notifications (component_id, type, message) VALUES (?, 'old_stock', ?)");
        $stmt->bind_param("is", $cid, $message);
        $stmt->execute();
    }
}

$conn->close();
?>
