<?php
session_start();
include 'db_connect.php';

// Check if user is admin
if (!isset($_SESSION['user_id']) || !$_SESSION['is_admin']) {
    header('Location: index.php');
    exit();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $transport_type = $_POST['transport_type'];
    $origin = $_POST['origin'];
    $destination = $_POST['destination'];
    $departure_time = $_POST['departure_time'];
    $total_seats = $_POST['total_seats'];
    $price = $_POST['price'];
    
    // Check if route already exists
    $check_stmt = $conn->prepare("SELECT route_id FROM Routes WHERE transport_type = ? AND origin = ? AND destination = ? AND departure_time = ?");
    $check_stmt->bind_param("ssss", $transport_type, $origin, $destination, $departure_time);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    
    if ($check_result->num_rows > 0) {
        $error = 'Route with same details already exists!';
    } else {
        // Insert new route
        $stmt = $conn->prepare("INSERT INTO Routes (transport_type, origin, destination, departure_time, total_seats, available_seats, price) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssiii", $transport_type, $origin, $destination, $departure_time, $total_seats, $total_seats, $price);
        
        if ($stmt->execute()) {
            $success = 'Route added successfully!';
        } else {
            $error = 'Failed to add route. Please try again.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Route - Swift Book</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <div class="form-container">
            <h1>➕ Add New Route</h1>
            
            <div class="actions" style="margin-bottom: 20px;">
                <a href="index.php" class="btn secondary">← Back to Dashboard</a>
            </div>
            
            <?php if ($error): ?>
                <div class="error"><?= $error ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="success"><?= $success ?></div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="form-group">
                    <label for="transport_type">Transport Type:</label>
                    <select id="transport_type" name="transport_type" required>
                        <option value="">Select Transport Type</option>
                        <option value="Bus">Bus</option>
                        <option value="Train">Train</option>
                        <option value="Airline">Airline</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="origin">Origin:</label>
                    <input type="text" id="origin" name="origin" required placeholder="e.g., Dhaka">
                </div>
                
                <div class="form-group">
                    <label for="destination">Destination:</label>
                    <input type="text" id="destination" name="destination" required placeholder="e.g., Chittagong">
                </div>
                
                <div class="form-group">
                    <label for="departure_time">Departure Time:</label>
                    <input type="time" id="departure_time" name="departure_time" required>
                </div>
                
                <div class="form-group">
                    <label for="total_seats">Total Seats:</label>
                    <input type="number" id="total_seats" name="total_seats" min="1" max="500" required placeholder="e.g., 50">
                </div>
                
                <div class="form-group">
                    <label for="price">Price (৳):</label>
                    <input type="number" id="price" name="price" min="0" step="0.01" required placeholder="e.g., 300.00">
                </div>
                
                <button type="submit" class="btn" style="width: 100%;">Add Route</button>
            </form>
        </div>
    </div>
</body>
</html>
