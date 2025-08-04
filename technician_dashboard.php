<?php
session_start();

// Only allow if logged in as technician
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'technician') {
    header("Location: login.php");
    exit();
}

$conn = new mysqli("localhost", "root", "root", "electronics_lab_inventory", 3307);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$message = "";

// Handle outward submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_outward'])) {
    $component_id = $_POST['component_id'];
    $quantity = intval($_POST['quantity']);
    $project = $_POST['project'];
    $user_id = getUserId($_SESSION['username'], $conn);

    // Get current quantity
    $qty_check = $conn->prepare("SELECT quantity FROM components WHERE id = ?");
    $qty_check->bind_param("i", $component_id);
    $qty_check->execute();
    $qty_check->bind_result($current_qty);
    $qty_check->fetch();
    $qty_check->close();

    if ($quantity > $current_qty) {
        $message = "❌ Not enough stock available.";
    } else {
        // Insert into logs
        $insert_log = $conn->prepare("INSERT INTO inward_outward_logs (component_id, user_id, action, quantity, project, timestamp) VALUES (?, ?, 'outward', ?, ?, NOW())");
        $insert_log->bind_param("iiis", $component_id, $user_id, $quantity, $project);
        $insert_log->execute();
        $insert_log->close();

        // Update quantity
        $update_qty = $conn->prepare("UPDATE components SET quantity = quantity - ? WHERE id = ?");
        $update_qty->bind_param("ii", $quantity, $component_id);
        $update_qty->execute();
        $update_qty->close();

        $message = "✅ Outward log recorded successfully.";
    }
}

// Get all components
$components = $conn->query("SELECT * FROM components ORDER BY id DESC");

// Helper: Get user ID from username
function getUserId($username, $conn) {
    $user_id = null;
    
    if ($stmt = $conn->prepare("SELECT id FROM users WHERE username = ?")) {
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->store_result(); // <-- Needed before bind_result
        $stmt->bind_result($user_id);
        $stmt->fetch();
        $stmt->close();
    }

    return $user_id;
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Technician Dashboard - Outward</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #0b1d3a;
            color: #ffffff;
            margin: 0;
            padding: 0;
        }

        .navbar {
            background-color: #102647;
            padding: 15px;
            display: flex;
            justify-content: space-between;
        }

        .navbar a {
            color: #1dc997;
            margin-right: 15px;
            text-decoration: none;
            font-weight: bold;
        }

        .container {
            margin: 30px auto;
            max-width: 1000px;
            background-color: #1c2e4a;
            padding: 20px;
            border-radius: 10px;
        }

        h2 {
            color: #1dc997;
            text-align: center;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background: #2c3e50;
        }

        th, td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #1dc997;
        }

        th {
            background-color: #102647;
            color: #1dc997;
        }

        input[type="number"], input[type="text"] {
            padding: 5px;
            width: 80px;
            background: #203040;
            color: white;
            border: none;
            border-radius: 4px;
        }

        input[type="submit"] {
            padding: 6px 12px;
            background-color: #1dc997;
            color: #102647;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        .message {
            color: #1dc997;
            text-align: center;
            margin-bottom: 15px;
            font-weight: bold;
        }
    </style>
</head>
<body>

    <div class="navbar">
        <div><strong>Technician Dashboard</strong></div>
        <div>
            <a href="inventory.php">Inventory</a>
            <a href="inward_outward.php">Logs</a>
            <a href="logout.php">Logout</a>
        </div>
    </div>

    <div class="container">
        <h2>Outward Component Dispatch</h2>

        <?php if (!empty($message)): ?>
            <div class="message"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>

        <table>
            <tr>
                <th>Component Name</th>
                <th>category</th>
                <th>Available Quantity</th>
                <th>Dispatch Qty</th>
                <th>Project</th>
                <th>Action</th>
            </tr>
            <?php while ($row = $components->fetch_assoc()): ?>
                <tr>
                    <form method="POST" action="">
                        <td><?= htmlspecialchars($row['name']) ?></td>
                        <td><?= htmlspecialchars($row['category']) ?></td>
                        <td><?= $row['quantity'] ?></td>
                        <td>
                            <input type="number" name="quantity" min="1" max="<?= $row['quantity'] ?>" required>
                        </td>
                        <td>
                            <input type="text" name="project" required>
                        </td>
                        <td>
                            <input type="hidden" name="component_id" value="<?= $row['id'] ?>">
                            <input type="submit" name="submit_outward" value="Outward">
                        </td>
                    </form>
                </tr>
            <?php endwhile; ?>
        </table>
    </div>

</body>
</html>
