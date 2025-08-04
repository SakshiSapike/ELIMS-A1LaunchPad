<?php
session_start();

// Role check (simulate for demo)
$_SESSION['role'] = 'admin'; // comment this out in production

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Database connection

$conn = new mysqli("localhost", "root", "root", "electronics_lab_inventory", 3307);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard - ELIMS</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background-color: #f9f9f9;
            margin: 0;
            padding: 20px;
        }
        h2, h3 {
            color: #2c3e50;
        }
        .container {
            max-width: 1000px;
            margin: auto;
        }
        section {
            background: #fff;
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 20px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.1);
        }
        form input, form select {
            padding: 10px;
            width: 100%;
            margin-bottom: 10px;
            border: 1px solid #ccc;
            border-radius: 6px;
        }
        button {
            background-color: #3498db;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
        }
        button:hover {
            background-color: #2980b9;
        }
        ul {
            padding-left: 20px;
        }
        canvas {
            margin-top: 10px;
        }
    </style>
</head>
<body>
<div class="container">
    <h2>Welcome, Admin</h2>

    <!-- Add User -->
    <section>
        <h3>Add New User</h3>
        <form action="add_user.php" method="post">
            <input type="text" name="username" placeholder="Username" required>
            <input type="password" name="password" placeholder="Password" required>
            <select name="role" required>
                <option value="">--Select Role--</option>
                <option value="user">User</option>
                <option value="technician">Lab Technician</option>
                <option value="researcher">Researcher</option>
                <option value="engineer">Manufacturing Engineer</option>
                <option value="admin">Admin</option>
            </select>
            <button type="submit">Add User</button>
        </form>
    </section>

    <!-- Set Threshold -->
    <section>
        <h3>Set Critical Low Threshold</h3>
        <form action="update_threshold.php" method="post">
            <select name="component_id" required>
                <?php
                $components = $conn->query("SELECT id, component_name FROM components");
                while ($comp = $components->fetch_assoc()) {
                    echo "<option value='{$comp['id']}'>{$comp['component_name']}</option>";
                }
                ?>
            </select>
            <input type="number" name="threshold" placeholder="Threshold" required>
            <button type="submit">Update</button>
        </form>
    </section>

    <!-- Inward Component -->
    <section>
        <h3>Quick Inward Entry</h3>
        <form action="inward_component.php" method="post">
            <select name="component_id" required>
                <?php
                $components->data_seek(0);
                while ($comp = $components->fetch_assoc()) {
                    echo "<option value='{$comp['id']}'>{$comp['component_name']}</option>";
                }
                ?>
            </select>
            <input type="number" name="quantity" placeholder="Quantity" required>
            <input type="text" name="reason" placeholder="Reason/Project">
            <button type="submit">Inward</button>
        </form>
    </section>

    <!-- Search Resistor -->
    <section>
        <h3>Search Resistor</h3>
        <form action="search_component.php" method="get">
            <input type="text" name="query" placeholder="e.g., 10k Resistor" required>
            <button type="submit">Search</button>
        </form>
    </section>

    <!-- Log Usage -->
    <section>
        <h3>Log Component Usage (Production)</h3>
        <form action="outward_component.php" method="post">
            <select name="component_id" required>
                <?php
                $components->data_seek(0);
                while ($comp = $components->fetch_assoc()) {
                    echo "<option value='{$comp['id']}'>{$comp['component_name']}</option>";
                }
                ?>
            </select>
            <input type="number" name="quantity" placeholder="Used Quantity" required>
            <input type="text" name="reason" placeholder="Batch/Project Name" required>
            <button type="submit">Log Usage</button>
        </form>
    </section>

    <!-- Graphs -->
    <section>
        <h3>Monthly Component Activity</h3>
        <canvas id="inwardChart" width="400" height="200"></canvas>
        <canvas id="outwardChart" width="400" height="200"></canvas>
    </section>

    <!-- Alerts -->
    <section>
        <h3>Alerts</h3>
        <h4>⚠️ Low Stock Components</h4>
        <ul>
            <?php
            $lowStock = $conn->query("SELECT component_name, quantity FROM components WHERE quantity <= critical_low_threshold");
            while ($row = $lowStock->fetch_assoc()) {
                echo "<li>{$row['component_name']} - Qty: {$row['quantity']}</li>";
            }
            ?>
        </ul>

        <h4>🕒 Old Stock (Not used in 3+ months)</h4>
        <ul>
            <?php
            $oldStockQuery = "
                SELECT c.component_name, MAX(io.timestamp) as last_used
                FROM inward_outward_log io
                JOIN components c ON io.component_id = c.id
                GROUP BY io.component_id
                HAVING last_used < NOW() - INTERVAL 3 MONTH
            ";
            $oldStock = $conn->query($oldStockQuery);
            while ($row = $oldStock->fetch_assoc()) {
                echo "<li>{$row['component_name']} - Last used: {$row['last_used']}</li>";
            }
            ?>
        </ul>
    </section>
</div>

<!-- Chart.js Script -->
<script>
const inwardChart = new Chart(document.getElementById('inwardChart').getContext('2d'), {
    type: 'bar',
    data: {
        labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul'],
        datasets: [{
            label: 'Inwarded',
            data: [12, 19, 3, 5, 2, 3, 8], // Replace with dynamic PHP if needed
            backgroundColor: 'rgba(54, 162, 235, 0.6)'
        }]
    }
});

const outwardChart = new Chart(document.getElementById('outwardChart').getContext('2d'), {
    type: 'bar',
    data: {
        labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul'],
        datasets: [{
            label: 'Outwarded',
            data: [5, 10, 2, 8, 3, 7, 4], // Replace with dynamic PHP if needed
            backgroundColor: 'rgba(255, 99, 132, 0.6)'
        }]
    }
});
</script>
</body>
</html>
