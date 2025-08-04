<?php
session_start();
if ($_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$conn = new mysqli("localhost", "root", "root", "electronics_lab_inventory", 3307);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle new user addition
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['add_user'])) {
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = $_POST['role'];

    $stmt = $conn->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $username, $password, $role);
    $stmt->execute();
    $stmt->close();
}

// Handle task assignment
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['assign_task'])) {
    $component_id = $_POST['component_id'];
    $message = $_POST['task_message'];
    $type = 'low_stock'; // or old_stock based on logic

    $stmt = $conn->prepare("INSERT INTO notifications (component_id, type, message, is_read, created_at) VALUES (?, ?, ?, 0, NOW())");
    $stmt->bind_param("iss", $component_id, $type, $message);
    $stmt->execute();
    $stmt->close();
}

// Fetch component-wise inward/outward
$component_sql = "
    SELECT c.name AS component, 
           SUM(CASE WHEN l.action = 'inward' THEN l.quantity ELSE 0 END) AS inward_qty,
           SUM(CASE WHEN l.action = 'outward' THEN l.quantity ELSE 0 END) AS outward_qty
    FROM inward_outward_logs l
    JOIN components c ON l.component_id = c.id
    GROUP BY c.name
";
$component_result = $conn->query($component_sql);

// Fetch daily inward/outward
$daily_sql = "
    SELECT DATE(timestamp) as date, 
           SUM(CASE WHEN action='inward' THEN quantity ELSE 0 END) as inward,
           SUM(CASE WHEN action='outward' THEN quantity ELSE 0 END) as outward
    FROM inward_outward_logs
    GROUP BY DATE(timestamp)
    ORDER BY DATE(timestamp)
";
$daily_result = $conn->query($daily_sql);

// Fetch notifications
$notif_result = $conn->query("SELECT n.id, n.message, n.type, n.is_read, n.created_at, c.name AS component_name
                              FROM notifications n
                              JOIN components c ON n.component_id = c.id
                              ORDER BY n.created_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard - ELIMS</title>
    <link rel="stylesheet" href="assets/styles.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
            font-family: sans-serif;
            margin: 20px;
            background-color: #f5f5f5;
            color: #333;
            transition: background 0.3s, color 0.3s;
        }
        .dark-mode {
            background-color: #121212;
            color: #eee;
        }
        button.toggle-mode {
            padding: 8px 14px;
            background: #333;
            color: #fff;
            border: none;
            margin-bottom: 20px;
            cursor: pointer;
            border-radius: 6px;
        }
        .chart-wrapper {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            justify-content: space-between;
            margin-bottom: 30px;
        }
        .chart-container {
            flex: 1 1 48%;
            min-width: 300px;
            height: 300px;
        }
        canvas {
            width: 100% !important;
            height: 100% !important;
        }
        .form-box {
            margin-top: 30px;
            background: #eee;
            padding: 15px;
            border-radius: 8px;
            max-width: 500px;
        }
        .form-box input, .form-box select {
            padding: 8px;
            width: 100%;
            margin-bottom: 10px;
        }
        .form-box button {
            padding: 8px 12px;
            background: #007bff;
            color: #fff;
            border: none;
            border-radius: 5px;
        }
        .notification {
            padding: 10px;
            margin: 10px 0;
            background: #fff3cd;
            border-left: 5px solid #ffc107;
            border-radius: 5px;
        }
        .notification.old_stock {
            border-left-color: #e74c3c;
        }
        .notification.low_stock {
            border-left-color: #f39c12;
        }
        h3 {
            margin-top: 40px;
        }
    </style>
</head>
<body>
    <div style="
    background-color: white;
    padding: 15px 25px;
    border-radius: 10px;
    display: flex;
    gap: 30px;
    justify-content: flex-start;
    align-items: center;
    font-size: 18px;
    font-weight: bold;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
">
    <a href="inward_outward.php" style="
        color: #16e09f;
        text-decoration: none;
        transition: color 0.3s ease;
    " onmouseover="this.style.color='#0ba677'" onmouseout="this.style.color='#16e09f'">🔄 Logs</a>

    <a href="users.php" style="
        color: #16e09f;
        text-decoration: none;
        transition: color 0.3s ease;
    " onmouseover="this.style.color='#0ba677'" onmouseout="this.style.color='#16e09f'">👤 Users</a>
</div>

<h2>📊 Admin Dashboard - ELIMS</h2>

<!-- Graphs -->
<div class="chart-wrapper">
    <div class="chart-container">
        <canvas id="componentChart"></canvas>
    </div>
    <div class="chart-container">
        <canvas id="dailyChart"></canvas>
    </div>
</div>

<!-- Add User Form -->
<div class="form-box">
    <h3>👤 Add New User</h3>
    <form method="POST">
        <input type="text" name="username" placeholder="Username" required>
        <input type="password" name="password" placeholder="Password" required>
        <select name="role">
            <option value="technician">Technician</option>
            <option value="admin">Admin</option>
        </select>
        <button type="submit" name="add_user">Add User</button>
    </form>
</div>

<!-- Assign Task Form -->
<div class="form-box">
    <h3>🛠️ Assign Task to Technician</h3>
    <form method="POST">
        <select name="component_id" required>
            <?php
            $comp_res = $conn->query("SELECT id, name FROM components");
            while ($row = $comp_res->fetch_assoc()) {
                echo "<option value='{$row['id']}'>{$row['name']}</option>";
            }
            ?>
        </select>
        <input type="text" name="task_message" placeholder="Task details" required>
        <button type="submit" name="assign_task">Assign Task</button>
    </form>
</div>

<!-- Notifications -->
<h3>🔔 Notifications</h3>
<?php if ($notif_result->num_rows > 0): ?>
    <?php while ($row = $notif_result->fetch_assoc()): ?>
        <div class="notification <?= $row['type'] ?>">
            <strong>🔧 <?= htmlspecialchars($row['component_name']) ?>:</strong>
            <?= htmlspecialchars($row['message']) ?>
            <div style="font-size: 0.8em; color: #555;">🕒 <?= $row['created_at'] ?></div>
        </div>
    <?php endwhile; ?>
<?php else: ?>
    <p>No notifications available.</p>
<?php endif; ?>

<!-- Scripts -->
<script>
   

    // Component Chart
    const compLabels = [], inwardData = [], outwardData = [];
    <?php $component_result->data_seek(0); while ($row = $component_result->fetch_assoc()) { ?>
        compLabels.push("<?= $row['component'] ?>");
        inwardData.push(<?= $row['inward_qty'] ?>);
        outwardData.push(<?= $row['outward_qty'] ?>);
    <?php } ?>

    const ctx1 = document.getElementById("componentChart").getContext("2d");
    new Chart(ctx1, {
        type: "bar",
        data: {
            labels: compLabels,
            datasets: [
                { label: "Inward", data: inwardData, backgroundColor: "rgba(54, 162, 235, 0.6)" },
                { label: "Outward", data: outwardData, backgroundColor: "rgba(255, 99, 132, 0.6)" }
            ]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { position: "top" },
                title: { display: true, text: "Component-wise Stock Movement" }
            }
        }
    });

    // Daily Chart
    const dailyLabels = [], dailyInward = [], dailyOutward = [];
    <?php $daily_result->data_seek(0); while ($row = $daily_result->fetch_assoc()) { ?>
        dailyLabels.push("<?= $row['date'] ?>");
        dailyInward.push(<?= $row['inward'] ?>);
        dailyOutward.push(<?= $row['outward'] ?>);
    <?php } ?>

    const ctx2 = document.getElementById("dailyChart").getContext("2d");
    new Chart(ctx2, {
        type: "line",
        data: {
            labels: dailyLabels,
            datasets: [
                {
                    label: "Inward",
                    data: dailyInward,
                    borderColor: "rgba(54, 162, 235, 1)",
                    backgroundColor: "rgba(54, 162, 235, 0.2)",
                    fill: true
                },
                {
                    label: "Outward",
                    data: dailyOutward,
                    borderColor: "rgba(255, 99, 132, 1)",
                    backgroundColor: "rgba(255, 99, 132, 0.2)",
                    fill: true
                }
            ]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { position: "top" },
                title: { display: true, text: "Daily Inward vs Outward Flow" }
            }
        }
    });
</script>
</body>
</html>
