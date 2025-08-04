<?php
// Connect to DB
$conn = new mysqli("localhost", "root", "root", "electronics_lab_inventory", 3307);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$id = $_GET['id'] ?? null;
if (!$id) {
    echo "❌ Invalid request.";
    exit();
}

$message = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $manufacturer = $_POST['manufacturer'];
    $part_number = $_POST['part_number'];
    $description = $_POST['description'];
    $quantity = $_POST['quantity'];
    $location = $_POST['location'];
    $unit_price = $_POST['unit_price'];
    $datasheet_url = $_POST['datasheet_url'];
    $category = $_POST['category'];
    $critical_low = $_POST['critical_low'];

    $stmt = $conn->prepare("UPDATE components SET name=?, manufacturer=?, part_number=?, description=?, quantity=?, location=?, unit_price=?, datasheet_url=?, category=?, critical_low=? WHERE id=?");
    $stmt->bind_param("ssssissssii", $name, $manufacturer, $part_number, $description, $quantity, $location, $unit_price, $datasheet_url, $category, $critical_low, $id);

    if ($stmt->execute()) {
        $message = "✅ Component updated successfully.";
    } else {
        $message = "❌ Failed to update component: " . $stmt->error;
    }
    $stmt->close();
}

// Get existing data
$result = $conn->query("SELECT * FROM components WHERE id = $id");
if ($result->num_rows != 1) {
    echo "❌ Component not found.";
    exit();
}
$data = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Component | ELIMS</title>
    <style>
        body {
            background-color: #0b1d3a;
            color: white;
            font-family: 'Segoe UI', sans-serif;
            padding: 40px;
        }

        h2 {
            color: #16e09f;
            margin-bottom: 20px;
        }

        form {
            background-color: #102647;
            padding: 30px;
            border-radius: 10px;
            max-width: 600px;
            margin: auto;
            box-shadow: 0 0 15px #16e09f80;
        }

        label {
            display: block;
            margin-bottom: 15px;
            color: #ffffff;
        }

        input, textarea {
            width: 100%;
            padding: 10px;
            background-color: #1a273b;
            color: white;
            border: none;
            border-radius: 5px;
            margin-top: 5px;
        }

        button {
            background-color: #16e09f;
            color: #0b1d3a;
            padding: 10px 20px;
            font-weight: bold;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        p {
            text-align: center;
            margin-top: 20px;
        }

        a {
            color: #16e09f;
            text-decoration: none;
        }

        .msg {
            text-align: center;
            font-weight: bold;
            margin-bottom: 20px;
        }

        .success {
            color: #16e09f;
        }

        .error {
            color: #ff4c4c;
        }
    </style>
</head>
<body>
    <h2>✏️ Edit Component</h2>
    <?php if ($message): ?>
        <p class="msg <?= strpos($message, '✅') !== false ? 'success' : 'error' ?>"><?= $message ?></p>
    <?php endif; ?>
    <form method="post">
        <label>Name:
            <input type="text" name="name" value="<?= htmlspecialchars($data['name']) ?>" required>
        </label>
        <label>Manufacturer:
            <input type="text" name="manufacturer" value="<?= htmlspecialchars($data['manufacturer']) ?>">
        </label>
        <label>Part Number:
            <input type="text" name="part_number" value="<?= htmlspecialchars($data['part_number']) ?>">
        </label>
        <label>Description:
            <textarea name="description"><?= htmlspecialchars($data['description']) ?></textarea>
        </label>
        <label>Quantity:
            <input type="number" name="quantity" value="<?= $data['quantity'] ?>" required>
        </label>
        <label>Location:
            <input type="text" name="location" value="<?= htmlspecialchars($data['location']) ?>">
        </label>
        <label>Unit Price:
            <input type="text" name="unit_price" value="<?= $data['unit_price'] ?>">
        </label>
        <label>Datasheet URL:
            <input type="text" name="datasheet_url" value="<?= htmlspecialchars($data['datasheet_url']) ?>">
        </label>
        <label>Category:
            <input type="text" name="category" value="<?= htmlspecialchars($data['category']) ?>">
        </label>
        <label>Critical Low:
            <input type="number" name="critical_low" value="<?= $data['critical_low'] ?>">
        </label><br>
        <button type="submit">Update Component</button>
    </form>
    <p><a href="inventory.php">← Back to Inventory</a></p>
</body>
</html>
