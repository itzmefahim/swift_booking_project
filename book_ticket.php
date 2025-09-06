<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$error = '';
$success = '';
$route_id = $_GET['route_id'] ?? '';

// Get route details if route_id is provided
$selected_route = null;
if ($route_id) {
    $stmt = $conn->prepare("SELECT * FROM Routes WHERE route_id = ? AND status = 'Active'");
    $stmt->bind_param("i", $route_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $selected_route = $result->fetch_assoc();
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $route_id = $_POST['route_id'];
    $seat_number = $_POST['seat_number'];
    
    // Check if route exists and has available seats
    $stmt = $conn->prepare("SELECT * FROM Routes WHERE route_id = ? AND status = 'Active' AND available_seats > 0");
    $stmt->bind_param("i", $route_id);
    $stmt->execute();
    $route_result = $stmt->get_result();
    
    if ($route_result->num_rows == 0) {
        $error = 'Route not available or fully booked!';
    } else {
        $route = $route_result->fetch_assoc();
        
        // Check if seat is valid
        if ($seat_number < 1 || $seat_number > $route['total_seats']) {
            $error = 'Invalid seat number!';
        } else {
            // Check if seat is already booked
            $seat_check = $conn->prepare("SELECT booking_id FROM Bookings WHERE route_id = ? AND seat_number = ? AND status = 'Confirmed'");
            $seat_check->bind_param("ii", $route_id, $seat_number);
            $seat_check->execute();
            $seat_result = $seat_check->get_result();
            
            if ($seat_result->num_rows > 0) {
                $error = 'Seat already booked! Please choose another seat.';
            } else {
                // Start transaction
                $conn->begin_transaction();
                
                try {
                    // Insert booking
                    $booking_stmt = $conn->prepare("INSERT INTO Bookings (user_id, route_id, seat_number) VALUES (?, ?, ?)");
                    $booking_stmt->bind_param("iii", $user_id, $route_id, $seat_number);
                    $booking_stmt->execute();
                    $booking_id = $conn->insert_id;
                    
                    // Update available seats
                    $update_stmt = $conn->prepare("UPDATE Routes SET available_seats = available_seats - 1 WHERE route_id = ?");
                    $update_stmt->bind_param("i", $route_id);
                    $update_stmt->execute();
                    
                    // Insert payment record
                    $payment_stmt = $conn->prepare("INSERT INTO Payments (booking_id, amount) VALUES (?, ?)");
                    $payment_stmt->bind_param("id", $booking_id, $route['price']);
                    $payment_stmt->execute();
                    
                    $conn->commit();
                    $success = "Ticket booked successfully! Booking ID: $booking_id";
                    
                } catch (Exception $e) {
                    $conn->rollback();
                    $error = 'Booking failed. Please try again.';
                }
            }
        }
    }
}

// Get all available routes for dropdown
$routes_query = "SELECT * FROM Routes WHERE status = 'Active' AND available_seats > 0 ORDER BY transport_type, origin, destination";
$routes_result = $conn->query($routes_query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Ticket - Swift Book</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <div class="form-container">
            <h1>🎫 Book Ticket</h1>
            
            <div class="actions" style="margin-bottom: 20px;">
                <a href="index.php" class="btn secondary">← Back to Dashboard</a>
                <a href="view_routes.php" class="btn secondary">View All Routes</a>
            </div>
            
            <?php if ($error): ?>
                <div class="error"><?= $error ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="success"><?= $success ?></div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="form-group">
                    <label for="route_id">Select Route:</label>
                    <select id="route_id" name="route_id" required onchange="updateRouteDetails()">
                        <option value="">Choose a route...</option>
                        <?php while ($route = $routes_result->fetch_assoc()): ?>
                            <option value="<?= $route['route_id'] ?>" 
                                    data-price="<?= $route['price'] ?>"
                                    data-seats="<?= $route['total_seats'] ?>"
                                    data-available="<?= $route['available_seats'] ?>"
                                    <?= $selected_route && $selected_route['route_id'] == $route['route_id'] ? 'selected' : '' ?>>
                                <?= $route['transport_type'] ?> - <?= htmlspecialchars($route['origin']) ?> to <?= htmlspecialchars($route['destination']) ?> 
                                (<?= date('h:i A', strtotime($route['departure_time'])) ?>) - ৳<?= number_format($route['price'], 2) ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                
                <div id="route-details" style="display: <?= $selected_route ? 'block' : 'none' ?>;">
                    <div class="route-card">
                        <h3 id="route-info">Route Information</h3>
                        <p><strong>Price:</strong> ৳<span id="route-price"><?= $selected_route ? number_format($selected_route['price'], 2) : '0.00' ?></span></p>
                        <p><strong>Available Seats:</strong> <span id="available-seats"><?= $selected_route ? $selected_route['available_seats'] : '0' ?></span></p>
                        <p><strong>Total Seats:</strong> <span id="total-seats"><?= $selected_route ? $selected_route['total_seats'] : '0' ?></span></p>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="seat_number">Seat Number:</label>
                    <input type="number" id="seat_number" name="seat_number" min="1" max="<?= $selected_route ? $selected_route['total_seats'] : '1' ?>" required>
                    <small>Choose a seat number between 1 and <span id="max-seat"><?= $selected_route ? $selected_route['total_seats'] : '1' ?></span></small>
                </div>
                
                <button type="submit" class="btn" style="width: 100%;">Book Ticket</button>
            </form>
        </div>
    </div>
    
    <script>
    function updateRouteDetails() {
        const select = document.getElementById('route_id');
        const selectedOption = select.options[select.selectedIndex];
        const routeDetails = document.getElementById('route-details');
        
        if (selectedOption.value) {
            const price = selectedOption.getAttribute('data-price');
            const totalSeats = selectedOption.getAttribute('data-seats');
            const availableSeats = selectedOption.getAttribute('data-available');
            
            document.getElementById('route-price').textContent = parseFloat(price).toLocaleString('en-BD', {minimumFractionDigits: 2});
            document.getElementById('available-seats').textContent = availableSeats;
            document.getElementById('total-seats').textContent = totalSeats;
            document.getElementById('max-seat').textContent = totalSeats;
            document.getElementById('seat_number').setAttribute('max', totalSeats);
            
            routeDetails.style.display = 'block';
        } else {
            routeDetails.style.display = 'none';
        }
    }
    </script>
</body>
</html>
