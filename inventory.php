<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

$conn = new mysqli("localhost", "root", "root", "electronics_lab_inventory", 3307);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
include 'navigation.php';

// Add component
if (isset($_POST['add_component'])) {
    $stmt = $conn->prepare("INSERT INTO components (name, manufacturer, part_number, description, quantity, location, unit_price, datasheet_url, category, critical_low)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssissssi",
        $_POST['name'], $_POST['manufacturer'], $_POST['part_number'], $_POST['description'],
        $_POST['quantity'], $_POST['location'], $_POST['unit_price'], $_POST['datasheet_url'],
        $_POST['category'], $_POST['critical_low']
    );
    $stmt->execute();
    $stmt->close();
    header("Location: inventory.php");
    exit();
}

// Search and filter
$search = $_GET['search'] ?? '';
$category = $_GET['category'] ?? '';

$query = "SELECT * FROM components WHERE (name LIKE ? OR part_number LIKE ?)";
$params = ["%$search%", "%$search%"];

if (!empty($category)) {
    $query .= " AND category = ?";
    $params[] = $category;
}

$stmt = $conn->prepare($query);
if (count($params) == 3) {
    $stmt->bind_param("sss", $params[0], $params[1], $params[2]);
} else {
    $stmt->bind_param("ss", $params[0], $params[1]);
}
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Inventory | ELIMS</title>
    <style>
        body {
            background-color: #0b1d3a;
            color: white;
            font-family: 'Segoe UI', sans-serif;
            padding: 20px;
        }

        h2, h3 {
            color: #16e09f;
        }

        form, table {
            background-color: #102647;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            box-shadow: 0 0 10px #16e09f80;
        }

        input, select, textarea {
            width: 100%;
            padding: 8px;
            margin-top: 8px;
            margin-bottom: 12px;
            background: #1a273b;
            color: white;
            border: none;
            border-radius: 5px;
        }

        button {
            background-color: #16e09f;
            border: none;
            padding: 10px 15px;
            color: #0b1d3a;
            font-weight: bold;
            border-radius: 5px;
            cursor: pointer;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            color: #fff;
        }

        th, td {
            padding: 10px;
            border-bottom: 1px solid #333;
        }

        th {
            background-color: #1dc997;
            color: #102647;
        }

        .low-stock {
            background-color: #e74c3c;
            font-weight: bold;
        }

        .filter {
            display: flex;
            gap: 20px;
        }

        .actions a button {
            margin-right: 5px;
        }
    </style>
</head>
<body>

<h2>📦 Electronics Component Inventory</h2>

<form method="POST">
    <h3>Add New Component</h3>
    <input type="text" name="name" placeholder="Component Name" required>
    <input type="text" name="manufacturer" placeholder="Manufacturer">
    <input type="text" name="part_number" placeholder="Part Number">
    <textarea name="description" placeholder="Description"></textarea>
    <input type="number" name="quantity" placeholder="Quantity" required>
    <input type="number" name="critical_low" placeholder="Critical Low Alert (e.g. 5)" required>
    <input type="text" name="location" placeholder="Location (e.g. Shelf B3)">
    <input type="text" name="unit_price" placeholder="Unit Price (e.g. 12.50)">
    <input type="text" name="datasheet_url" placeholder="Datasheet URL">
    <input type="text" name="category" placeholder="Category (e.g. Resistor, Sensor)">
    <button type="submit" name="add_component">Add Component</button>
</form>

<form method="GET" class="filter">
    <input type="text" name="search" placeholder="Search by name or part #" value="<?= htmlspecialchars($search) ?>">
    <select name="category">
        <option value="">All Categories</option>
        <option value="Resistor" <?= $category == 'Resistor' ? 'selected' : '' ?>>Resistor</option>
        <option value="Capacitor" <?= $category == 'Capacitor' ? 'selected' : '' ?>>Capacitor</option>
        <option value="IC" <?= $category == 'IC' ? 'selected' : '' ?>>IC</option>
        <option value="Sensor" <?= $category == 'Sensor' ? 'selected' : '' ?>>Sensor</option>
        <option value="Other" <?= $category == 'Other' ? 'selected' : '' ?>>Other</option>
    </select>
    <button type="submit">Filter</button>
</form>

<h3>Component List</h3>
<?php if (isset($_GET['deleted'])): ?>
    <p style="color:lightgreen;">✅ Component deleted successfully!</p>
<?php endif; ?>

<table>
    <thead>
        <tr>
            <th>ID</th><th>Name</th><th>Part #</th><th>Qty</th><th>Min</th><th>Location</th><th>Price</th><th>Category</th><th>Actions</th>
        </tr>
    </thead>
    <tbody>
    <?php while ($row = $result->fetch_assoc()): ?>
        <tr class="<?= $row['quantity'] < $row['critical_low'] ? 'low-stock' : '' ?>">
            <td><?= $row['id'] ?></td>
            <td><?= htmlspecialchars($row['name']) ?></td>
            <td><?= htmlspecialchars($row['part_number']) ?></td>
            <td><?= $row['quantity'] ?></td>
            <td><?= $row['critical_low'] ?></td>
            <td><?= htmlspecialchars($row['location']) ?></td>
            <td>₹<?= $row['unit_price'] ?></td>
            <td><?= htmlspecialchars($row['category']) ?></td>
            <td class="actions">
                <a href="edit_component.php?id=<?= $row['id'] ?>"><button>Edit</button></a>
                <a href="delete_component.php?id=<?= $row['id'] ?>" onclick="return confirm('Are you sure?')"><button style="background-color: red;">Delete</button></a>
            </td>
        </tr>
    <?php endwhile; ?>
    </tbody>
</table>

</body>
</html>

<?php
$stmt->close();
$conn->close();
?>
