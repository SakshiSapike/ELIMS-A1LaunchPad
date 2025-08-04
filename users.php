<?php
session_start();

// Optional: Redirect if not logged i
$conn = new mysqli("localhost", "root", "root", "electronics_lab_inventory", 3307);
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

// Only admin can assign roles
$isAdmin = isset($_SESSION['role']) && $_SESSION['role'] === 'admin';

// Handle role assignment
if (
    $isAdmin &&
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    isset($_POST['user_id'], $_POST['new_role']) &&
    isset($_SESSION['user_id'])
) {
    $userId = (int)$_POST['user_id'];
    $newRole = $conn->real_escape_string($_POST['new_role']);

    // Prevent admin from changing their own role
    if ($userId != $_SESSION['user_id']) {
        $conn->query("UPDATE users SET role = '$newRole' WHERE id = $userId");
    }
}

// Filter
$role = $_GET['role'] ?? '';
$query = "SELECT * FROM users";
if ($role) {
    $query .= " WHERE role = '" . $conn->real_escape_string($role) . "'";
}
$query .= " ORDER BY created_at DESC";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>User Management</title>
    <style>
        body {
            background-color: #0b1d3a;
            font-family: 'Segoe UI', sans-serif;
            color: white;
            padding: 20px;
        }

        h2 {
            color: #16e09f;
        }

        form.filter-form {
            background: #102647;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            display: flex;
            gap: 15px;
            box-shadow: 0 0 10px #16e09f80;
        }

        select, button {
            background: #1a273b;
            color: white;
            padding: 8px;
            border: none;
            border-radius: 5px;
        }

        button {
            background-color: #16e09f;
            color: #0b1d3a;
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

        .admin {
            background-color: #8e44ad;
            color: white;
        }

        .technician {
            background-color: #2980b9;
            color: white;
        }

        .researcher {
            background-color: #27ae60;
            color: white;
        }

        .assign-role-form {
            display: inline-block;
        }

        @media(max-width: 768px) {
            form.filter-form {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>

<h2>👥 User List</h2>

<form method="GET" class="filter-form">
    <label for="role">Filter by Role:</label>
    <select name="role" id="role">
        <option value="">All</option>
        <option value="admin" <?= $role === 'admin' ? 'selected' : '' ?>>Admin</option>
        <option value="technician" <?= $role === 'technician' ? 'selected' : '' ?>>Technician</option>
        <option value="researcher" <?= $role === 'researcher' ? 'selected' : '' ?>>Researcher</option>
    </select>
    <button type="submit">🔍 Filter</button>
</form>

<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>👤 Username</th>
            <th>📧 Email</th>
            <th>🎓 Role</th>
            <th>🕒 Created At</th>
            <?php if ($isAdmin): ?>
                <th>⚙️ Assign Role</th>
            <?php endif; ?>
        </tr>
    </thead>
    <tbody>
        <?php while ($row = $result->fetch_assoc()): ?>
            <tr class="<?= htmlspecialchars($row['role']) ?>">
                <td><?= $row['id'] ?></td>
                <td><?= htmlspecialchars($row['username']) ?></td>
                <td><?= htmlspecialchars($row['email']) ?></td>
                <td><?= ucfirst($row['role']) ?></td>
                <td><?= $row['created_at'] ?></td>
                <?php if ($isAdmin): ?>
                    <td>
                        <?php if (isset($_SESSION['user_id']) && $row['id'] != $_SESSION['user_id']): ?>
                            <form method="POST" class="assign-role-form">
                                <input type="hidden" name="user_id" value="<?= $row['id'] ?>">
                                <select name="new_role" onchange="this.form.submit()">
                                    <option value="">-- Change --</option>
                                    <option value="admin">Admin</option>
                                    <option value="technician">Technician</option>
                                    <option value="researcher">Researcher</option>
                                </select>
                            </form>
                        <?php else: ?>
                            <i>(You)</i>
                        <?php endif; ?>
                    </td>
                <?php endif; ?>
            </tr>
        <?php endwhile; ?>
    </tbody>
</table>

<p style="margin-top: 20px;">
    <a href="inventory.php" style="color:#16e09f;">⬅ Back to Inventory</a>
</p>

</body>
</html>

<?php $conn->close(); ?>
