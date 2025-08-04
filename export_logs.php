<?php
$conn = new mysqli("localhost", "root", "root", "electronics_lab_inventory", 3307);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$format = $_GET['format'] ?? 'csv';

// ✅ Use the correct table name: inward_outward_logs
$query = "SELECT inward_outward_logs.*, components.name AS component_name, users.username 
          FROM inward_outward_logs 
          JOIN components ON inward_outward_logs.component_id = components.id 
          JOIN users ON inward_outward_logs.user_id = users.id 
          ORDER BY inward_outward_logs.timestamp DESC";

$result = $conn->query($query);

// ✅ Check for SQL error
if (!$result) {
    die("SQL Error: " . $conn->error);
}

$rows = [];
while ($row = $result->fetch_assoc()) {
    $rows[] = [
        'ID' => $row['id'],
        'Component' => $row['component_name'],
        'User' => $row['username'],
        'Action' => ucfirst($row['action']),
        'Quantity' => $row['quantity'],
        'Project' => $row['project'],
        'Timestamp' => $row['timestamp']
    ];
}

// ✅ Export to CSV
if ($format === 'csv') {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment;filename="logs.csv"');
    
    $output = fopen('php://output', 'w');
    if (count($rows)) {
        fputcsv($output, array_keys($rows[0]));
        foreach ($rows as $row) {
            fputcsv($output, $row);
        }
    }
    fclose($output);
    exit;
}

// ✅ Export to PDF
if ($format === 'pdf') {
    require_once('tcpdf/tcpdf.php');

    $pdf = new TCPDF();
    $pdf->SetCreator('ELIMS');
    $pdf->SetAuthor('ELIMS System');
    $pdf->SetTitle('Inward/Outward Log Report');
    $pdf->AddPage();

    $html = '<h2>Inward/Outward Log Report</h2>';
    $html .= '<table border="1" cellpadding="4">';
    $html .= '<thead><tr>
                <th>ID</th>
                <th>Component</th>
                <th>User</th>
                <th>Action</th>
                <th>Quantity</th>
                <th>Project</th>
                <th>Timestamp</th>
              </tr></thead><tbody>';

    foreach ($rows as $row) {
        $html .= "<tr>
                    <td>{$row['ID']}</td>
                    <td>{$row['Component']}</td>
                    <td>{$row['User']}</td>
                    <td>{$row['Action']}</td>
                    <td>{$row['Quantity']}</td>
                    <td>{$row['Project']}</td>
                    <td>{$row['Timestamp']}</td>
                  </tr>";
    }

    $html .= '</tbody></table>';
    $pdf->writeHTML($html, true, false, true, false, '');
    $pdf->Output('logs.pdf', 'D');
    exit;
}

echo "❌ Unsupported format.";
?>
