<?php
session_start();
$conn = new mysqli("localhost", "root", "root", "electronics_lab_inventory", 3307);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Only allow logged-in technician users
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'technician') {
    die("Access denied. Please login as a technician.");
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $component_id = $_POST['component_id'];
    $action = $_POST['action'];
    $quantity = $_POST['quantity'];
    $project = $_POST['project'];
    $user_id = $_SESSION['user_id'];

    $sql = "INSERT INTO inward_outward (component_id, user_id, action, quantity, project)
            VALUES (?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }

    $stmt->bind_param("iisis", $component_id, $user_id, $action, $quantity, $project);

    if ($stmt->execute()) {
        $message = "✅ Log added successfully!";
    } else {
        $message = "❌ Error: " . $stmt->error;
    }

    $stmt->close();
}

// Fetch components to populate dropdown
$components = [];
$result = $conn->query("SELECT id, name FROM components");
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $components[] = $row;
    }
}
$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add Component Log</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f6f9;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .form-container {
            background-color: white;
            padding: 30px 40px;
            border-radius: 12px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.15);
            width: 400px;
        }

        h2 {
            text-align: center;
            margin-bottom: 20px;
            color: #333;
        }

        label {
            display: block;
            margin-bottom: 6px;
            font-weight: bold;
        }

        select, input[type="number"], input[type="text"] {
            width: 100%;
            padding: 8px 10px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 6px;
        }

        input[type="submit"] {
            width: 100%;
            padding: 10px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            cursor: pointer;
        }

        input[type="submit"]:hover {
            background-color: #0056b3;
        }

        .message {
            margin-bottom: 15px;
            color: green;
            text-align: center;
            font-weight: bold;
        }
    </style>
</head>
<body>
<div class="form-container">
    <h2>Add Inward/Outward Log</h2>

    <?php if (!empty($message)) echo "<div class='message'>$message</div>"; ?>

    <form method="POST" action="">
        <label>Component:</label>
        <select name="component_id" required>
            <option value="">-- Select Component --</option>
            <?php foreach ($components as $component): ?>
                <option value="<?= $component['id'] ?>"><?= htmlspecialchars($component['name']) ?></option>
            <?php endforeach; ?>
        </select>

        <label>Action:</label>
        <select name="action" required>
            <option value="inward">Inward</option>
            <option value="outward">Outward</option>
        </select>

        <label>Quantity:</label>
        <input type="number" name="quantity" required min="1">

        <label>Project (optional):</label>
        <input type="text" name="project">

        <input type="submit" value="Submit Log">
    </form>
</div>
</body>
</html>
