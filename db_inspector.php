<?php
session_start();
include 'db_connect.php';

// Check if user is admin
if (!isset($_SESSION['user_id']) || !$_SESSION['is_admin']) {
    header('Location: index.php');
    exit();
}

$querySQL = '';
$resultRows = [];
$resultFields = [];
$errorMsg = '';

// Handle SQL query execution
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['sql'])) {
    $querySQL = trim($_POST['sql']);
    
    if (!empty($querySQL)) {
        // Only allow SELECT queries for security
        if (stripos($querySQL, 'SELECT') === 0) {
            try {
                $result = $conn->query($querySQL);
                if ($result) {
                    $resultRows = $result->fetch_all(MYSQLI_ASSOC);
                    $resultFields = $result->fetch_fields();
                } else {
                    $errorMsg = $conn->error;
                }
            } catch (Exception $e) {
                $errorMsg = $e->getMessage();
            }
        } else {
            $errorMsg = 'Only SELECT queries are allowed for security reasons.';
        }
    }
}

// Get database schema
$schema = [];
$tables = ['Users', 'Routes', 'Bookings', 'Payments'];

foreach ($tables as $table) {
    $result = $conn->query("DESCRIBE $table");
    $schema[$table] = $result->fetch_all(MYSQLI_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Inspector - Swift Book</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <h1>🗄️ Database Inspector</h1>
        
        <div class="actions">
            <a href="index.php" class="btn secondary">← Back to Dashboard</a>
        </div>
        
        <h2>📋 Database Schema</h2>
        <?php foreach ($schema as $tbl => $cols): ?>
            <details>
                <summary>📋 <?= $tbl ?> (<?= count($cols) ?> columns)</summary>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Field</th>
                                <th>Type</th>
                                <th>Key</th>
                                <th>Null</th>
                                <th>Default</th>
                                <th>Extra</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($cols as $c): ?>
                                <tr>
                                    <td><strong><?= $c['Field'] ?></strong></td>
                                    <td><?= $c['Type'] ?></td>
                                    <td><?= $c['Key'] ?></td>
                                    <td><?= $c['Null'] ?></td>
                                    <td><?= $c['Default'] ?></td>
                                    <td><?= $c['Extra'] ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </details>
        <?php endforeach; ?>
        
        <div style="margin-top: 30px;">
            <h2>🔧 Database Functions & Triggers</h2>
            <details>
                <summary>📋 Custom Functions</summary>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr><th>Function Name</th><th>Return Type</th><th>Description</th></tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><strong>CalculateRouteRevenue</strong></td>
                                <td>DECIMAL(10,2)</td>
                                <td>Calculates total revenue for a specific route</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <p><strong>Usage:</strong> <code>SELECT CalculateRouteRevenue(1) AS revenue;</code></p>
            </details>
            
            <details>
                <summary>⚡ Active Triggers</summary>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr><th>Trigger Name</th><th>Table</th><th>Event</th><th>Description</th></tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><strong>UpdateRouteStatus</strong></td>
                                <td>Routes</td>
                                <td>AFTER UPDATE</td>
                                <td>Auto-updates route status when seats become full/available</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </details>
        </div>
        
        <div class="console">
            <h2>🔍 SQL Query Console</h2>
            <p><strong>Sample Queries:</strong></p>
            <ul>
                <li><code>SELECT * FROM Users LIMIT 5</code></li>
                <li><code>SELECT * FROM Routes WHERE transport_type = 'Bus'</code></li>
                <li><code>SELECT u.username, COUNT(b.booking_id) as total_bookings FROM Users u LEFT JOIN Bookings b ON u.user_id = b.user_id GROUP BY u.user_id</code></li>
                <li><code>SELECT CalculateRouteRevenue(1) AS revenue</code></li>
            </ul>
            
            <form method="POST">
                <textarea name="sql" placeholder="Enter your SELECT query here..."><?= htmlspecialchars($querySQL) ?></textarea>
                <button type="submit" class="btn">▶️ Run Query</button>
            </form>
            
            <?php if ($errorMsg): ?>
                <p class="error">❌ Error: <?= $errorMsg ?></p>
            <?php elseif ($querySQL): ?>
                <h3>📊 Query Results (<?= count($resultRows) ?> rows)</h3>
                <?php if ($resultRows): ?>
                    <div class="table-container">
                        <table class="resultTable">
                            <thead>
                                <tr>
                                    <?php foreach ($resultFields as $f): ?>
                                        <th><?= $f->name ?></th>
                                    <?php endforeach; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($resultRows as $r): ?>
                                    <tr>
                                        <?php foreach ($r as $val): ?>
                                            <td><?= htmlspecialchars($val ?? 'NULL') ?></td>
                                        <?php endforeach; ?>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p>✅ Query executed successfully. No rows returned.</p>
                <?php endif; ?>
            <?php endif; ?>
        </div>
        
        <div style="margin-top: 30px;">
            <h2>📈 Quick Statistics</h2>
            <div class="stats-grid">
                <?php
                $quick_stats = [
                    'Total Users' => $conn->query("SELECT COUNT(*) as count FROM Users")->fetch_assoc()['count'],
                    'Active Routes' => $conn->query("SELECT COUNT(*) as count FROM Routes WHERE status = 'Active'")->fetch_assoc()['count'],
                    'Total Bookings' => $conn->query("SELECT COUNT(*) as count FROM Bookings WHERE status = 'Confirmed'")->fetch_assoc()['count'],
                    'Total Revenue' => '৳' . number_format($conn->query("SELECT SUM(amount) as total FROM Payments WHERE status = 'Completed'")->fetch_assoc()['total'] ?? 0, 2)
                ];
                
                foreach ($quick_stats as $label => $value): ?>
                    <div class="stat-card">
                        <div class="stat-number"><?= $value ?></div>
                        <div class="stat-label"><?= $label ?></div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</body>
</html>
