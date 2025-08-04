<?php
$conn = new mysqli("localhost", "root", "root", "electronics_lab_inventory", 3307);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$id = $_GET['id'] ?? null;
if (!$id) {
    echo "❌ Invalid request.";
    exit();
}

$stmt = $conn->prepare("DELETE FROM components WHERE id = ?");
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    header("Location: inventory.php?deleted=1");
} else {
    echo "❌ Failed to delete: " . $stmt->error;
}
$stmt->close();
$conn->close();
?>
