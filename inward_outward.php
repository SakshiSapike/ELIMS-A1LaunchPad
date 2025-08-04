<?php
session_start();
$conn = new mysqli("localhost", "root", "root", "electronics_lab_inventory", 3307);
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);
include 'navigation.php';
// Filters
$project = $_GET['project'] ?? '';
$component = $_GET['component'] ?? '';
$from = $_GET['from'] ?? '';
$to = $_GET['to'] ?? '';

// Build query (corrected table name)
$query = "SELECT inward_outward_logs.*, components.name AS component_name, users.username 
          FROM inward_outward_logs 
          JOIN components ON inward_outward_logs.component_id = components.id 
          JOIN users ON inward_outward_logs.user_id = users.id 
          WHERE 1";

if ($project) $query .= " AND inward_outward_logs.project LIKE '%$project%'";
if ($component) $query .= " AND components.name LIKE '%$component%'";
if ($from && $to) $query .= " AND DATE(inward_outward_logs.timestamp) BETWEEN '$from' AND '$to'";
$query .= " ORDER BY inward_outward_logs.timestamp DESC";

$result = $conn->query($query);
if (!$result) {
    die("❌ SQL Error: " . $conn->error);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Inward/Outward Log</title>
    <style>
        body {
            background-color: #0b1d3a;
            font-family: 'Segoe UI', sans-serif;
            color: white;
            padding: 20px;
        }
        h2 { color: #16e09f; }
        form {
            background: #102647;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            box-shadow: 0 0 10px #16e09f80;
        }
        input, select {
            background: #1a273b;
            color: white;
            padding: 8px;
            border: none;
            border-radius: 5px;
        }
        button {
            background-color: #16e09f;
            color: #0b1d3a;
            border: none;
            padding: 8px 12px;
            border-radius: 5px;
            font-weight: bold;
            cursor: pointer;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            background: #102647;
            box-shadow: 0 0 10px #16e09f50;
        }
        th, td {
            padding: 10px;
            border-bottom: 1px solid #2c3e50;
            text-align: center;
        }
        th {
            background-color: #1dc997;
            color: #0b1d3a;
        }
        .inward {
            background-color: #27ae60;
            color: white;
        }
        .outward {
            background-color: #c0392b;
            color: white;
        }
        .export-btn {
            margin-left: auto;
            display: flex;
            gap: 10px;
        }
        @media(max-width: 768px) {
            form {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>

<h2>📑 Inward/Outward Logs</h2>

<form method="GET">
    <input type="text" name="project" placeholder="Project name" value="<?= htmlspecialchars($project) ?>">
    <input type="text" name="component" placeholder="Component name" value="<?= htmlspecialchars($component) ?>">
    <input type="date" name="from" value="<?= $from ?>">
    <input type="date" name="to" value="<?= $to ?>">
    <button type="submit">🔍 Filter</button>
    <div class="export-btn">
        <a href="export_logs.php?format=csv" target="_blank"><button type="button">📁 Export CSV</button></a>
        <a href="export_logs.php?format=pdf" target="_blank"><button type="button">📄 Export PDF</button></a>
    </div>
</form>

<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Component</th>
            <th>User</th>
            <th>Action</th>
            <th>Qty</th>
            <th>Project</th>
            <th>Date/Time</th>
        </tr>
    </thead>
    <tbody>
        <?php while ($row = $result->fetch_assoc()): ?>
        <tr class="<?= $row['action'] ?>">
            <td><?= $row['id'] ?></td>
            <td><?= htmlspecialchars($row['component_name']) ?></td>
            <td><?= htmlspecialchars($row['username']) ?></td>
            <td><?= ucfirst($row['action']) ?></td>
            <td><?= $row['quantity'] ?></td>
            <td><?= htmlspecialchars($row['project']) ?></td>
            <td><?= $row['timestamp'] ?></td>
        </tr>
        <?php endwhile; ?>
    </tbody>
</table>

</body>
</html>

<?php $conn->close(); ?>
