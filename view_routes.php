<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Get filter parameters
$transport_type = $_GET['transport_type'] ?? '';
$origin = $_GET['origin'] ?? '';
$destination = $_GET['destination'] ?? '';

// Build query with filters
$where_conditions = ["status = 'Active'"];
$params = [];
$types = "";

if ($transport_type) {
    $where_conditions[] = "transport_type = ?";
    $params[] = $transport_type;
    $types .= "s";
}
if ($origin) {
    $where_conditions[] = "origin LIKE ?";
    $params[] = "%$origin%";
    $types .= "s";
}
if ($destination) {
    $where_conditions[] = "destination LIKE ?";
    $params[] = "%$destination%";
    $types .= "s";
}

$where_clause = implode(" AND ", $where_conditions);
$query = "SELECT * FROM Routes WHERE $where_clause ORDER BY transport_type, departure_time";

$stmt = $conn->prepare($query);
if ($params) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

// Get unique origins and destinations for filter dropdowns
$origins = $conn->query("SELECT DISTINCT origin FROM Routes WHERE status = 'Active' ORDER BY origin")->fetch_all(MYSQLI_ASSOC);
$destinations = $conn->query("SELECT DISTINCT destination FROM Routes WHERE status = 'Active' ORDER BY destination")->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Routes - Swift Book</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <h1>🚌 Available Routes</h1>
        
        <div class="actions">
            <a href="index.php" class="btn secondary">← Back to Dashboard</a>
        </div>
        
        <!-- Search Filters -->
        <div class="form-container" style="margin-bottom: 30px;">
            <form method="GET" class="search-form">
                <div class="form-group">
                    <label for="transport_type">Transport Type:</label>
                    <select id="transport_type" name="transport_type">
                        <option value="">All Types</option>
                        <option value="Bus" <?= $transport_type == 'Bus' ? 'selected' : '' ?>>Bus</option>
                        <option value="Train" <?= $transport_type == 'Train' ? 'selected' : '' ?>>Train</option>
                        <option value="Airline" <?= $transport_type == 'Airline' ? 'selected' : '' ?>>Airline</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="origin">From:</label>
                    <select id="origin" name="origin">
                        <option value="">Any Origin</option>
                        <?php foreach ($origins as $o): ?>
                            <option value="<?= $o['origin'] ?>" <?= $origin == $o['origin'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($o['origin']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="destination">To:</label>
                    <select id="destination" name="destination">
                        <option value="">Any Destination</option>
                        <?php foreach ($destinations as $d): ?>
                            <option value="<?= $d['destination'] ?>" <?= $destination == $d['destination'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($d['destination']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <button type="submit" class="btn">🔍 Search</button>
                <a href="view_routes.php" class="btn secondary">Clear</a>
            </form>
        </div>
        
        <!-- Routes Display -->
        <div class="routes-container">
            <?php if ($result->num_rows > 0): ?>
                <?php while ($route = $result->fetch_assoc()): ?>
                    <div class="route-card">
                        <div class="route-header">
                            <h3><?= htmlspecialchars($route['origin']) ?> → <?= htmlspecialchars($route['destination']) ?></h3>
                            <span class="route-type"><?= $route['transport_type'] ?></span>
                        </div>
                        
                        <div class="route-details">
                            <div class="detail-item">
                                <div class="detail-label">Departure Time</div>
                                <div class="detail-value"><?= date('h:i A', strtotime($route['departure_time'])) ?></div>
                            </div>
                            
                            <div class="detail-item">
                                <div class="detail-label">Available Seats</div>
                                <div class="detail-value"><?= $route['available_seats'] ?>/<?= $route['total_seats'] ?></div>
                            </div>
                            
                            <div class="detail-item">
                                <div class="detail-label">Price</div>
                                <div class="detail-value">৳<?= number_format($route['price'], 2) ?></div>
                            </div>
                            
                            <div class="detail-item">
                                <div class="detail-label">Action</div>
                                <div class="detail-value">
                                    <?php if ($route['available_seats'] > 0): ?>
                                        <a href="book_ticket.php?route_id=<?= $route['route_id'] ?>" class="btn">Book Now</a>
                                    <?php else: ?>
                                        <span style="color: #f44336;">Fully Booked</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="route-card">
                    <p class="center">No routes found matching your criteria.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
