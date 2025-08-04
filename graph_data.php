<?php
$conn = new mysqli("localhost", "root", "root", "electronics_lab_inventory", 3307);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$data = [
    'months' => [],
    'inward' => [],
    'outward' => []
];

$monthsQuery = "SELECT DISTINCT DATE_FORMAT(timestamp, '%Y-%m') as month FROM inward_outward_log ORDER BY month ASC";
$monthsRes = $conn->query($monthsQuery);

while ($row = $monthsRes->fetch_assoc()) {
    $month = $row['month'];
    $data['months'][] = $month;

    $inward = $conn->query("SELECT SUM(quantity) as total FROM inward_outward_log WHERE action='inward' AND DATE_FORMAT(timestamp, '%Y-%m')='$month'")->fetch_assoc()['total'] ?? 0;
    $outward = $conn->query("SELECT SUM(quantity) as total FROM inward_outward_log WHERE action='outward' AND DATE_FORMAT(timestamp, '%Y-%m')='$month'")->fetch_assoc()['total'] ?? 0;

    $data['inward'][] = (int)$inward;
    $data['outward'][] = (int)$outward;
}

header('Content-Type: application/json');
echo json_encode($data);
?>
