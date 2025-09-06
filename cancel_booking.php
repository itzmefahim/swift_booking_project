<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$is_admin = $_SESSION['is_admin'];
$booking_id = $_GET['id'] ?? '';

if (!$booking_id) {
    header('Location: my_bookings.php');
    exit();
}

// Get booking details
$stmt = $conn->prepare("SELECT b.*, r.transport_type, r.origin, r.destination 
                        FROM Bookings b 
                        JOIN Routes r ON b.route_id = r.route_id 
                        WHERE b.booking_id = ? AND (b.user_id = ? OR ?)");
$stmt->bind_param("iii", $booking_id, $user_id, $is_admin);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    header('Location: my_bookings.php');
    exit();
}

$booking = $result->fetch_assoc();

if ($booking['status'] == 'Cancelled') {
    header('Location: my_bookings.php');
    exit();
}

// Process cancellation
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $conn->begin_transaction();
    
    try {
        // Update booking status
        $cancel_stmt = $conn->prepare("UPDATE Bookings SET status = 'Cancelled' WHERE booking_id = ?");
        $cancel_stmt->bind_param("i", $booking_id);
        $cancel_stmt->execute();
        
        // Increase available seats
        $seat_stmt = $conn->prepare("UPDATE Routes SET available_seats = available_seats + 1 WHERE route_id = ?");
        $seat_stmt->bind_param("i", $booking['route_id']);
        $seat_stmt->execute();
        
        // Update payment status
        $payment_stmt = $conn->prepare("UPDATE Payments SET status = 'Failed' WHERE booking_id = ?");
        $payment_stmt->bind_param("i", $booking_id);
        $payment_stmt->execute();
        
        $conn->commit();
        
        // Redirect with success message
        header('Location: my_bookings.php?cancelled=1');
        exit();
        
    } catch (Exception $e) {
        $conn->rollback();
        $error = 'Cancellation failed. Please try again.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cancel Booking - Swift Book</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <div class="form-container">
            <h1>❌ Cancel Booking</h1>
            
            <div class="actions" style="margin-bottom: 20px;">
                <a href="my_bookings.php" class="btn secondary">← Back to My Bookings</a>
            </div>
            
            <?php if (isset($error)): ?>
                <div class="error"><?= $error ?></div>
            <?php endif; ?>
            
            <div class="route-card">
                <h3>Booking Details</h3>
                <div class="route-details">
                    <div class="detail-item">
                        <div class="detail-label">Booking ID</div>
                        <div class="detail-value">#<?= $booking['booking_id'] ?></div>
                    </div>
                    
                    <div class="detail-item">
                        <div class="detail-label">Transport</div>
                        <div class="detail-value"><?= $booking['transport_type'] ?></div>
                    </div>
                    
                    <div class="detail-item">
                        <div class="detail-label">Route</div>
                        <div class="detail-value"><?= htmlspecialchars($booking['origin']) ?> → <?= htmlspecialchars($booking['destination']) ?></div>
                    </div>
                    
                    <div class="detail-item">
                        <div class="detail-label">Seat Number</div>
                        <div class="detail-value">#<?= $booking['seat_number'] ?></div>
                    </div>
                    
                    <div class="detail-item">
                        <div class="detail-label">Booking Date</div>
                        <div class="detail-value"><?= date('M d, Y h:i A', strtotime($booking['booking_date'])) ?></div>
                    </div>
                </div>
            </div>
            
            <div style="background: #ffebee; padding: 20px; border-radius: 10px; margin: 20px 0;">
                <h3 style="color: #f44336;">⚠️ Cancellation Policy</h3>
                <ul style="color: #666; margin-top: 10px;">
                    <li>Booking will be cancelled immediately</li>
                    <li>Seat will be made available for other passengers</li>
                    <li>Refund processing may take 3-5 business days</li>
                    <li>Cancellation charges may apply as per terms</li>
                </ul>
            </div>
            
            <form method="POST">
                <div style="display: flex; gap: 15px; flex-wrap: wrap;">
                    <button type="submit" class="btn danger" style="flex: 1; min-width: 200px;"
                            onclick="return confirm('Are you sure you want to cancel this booking? This action cannot be undone.');">
                        ❌ Confirm Cancellation
                    </button>
                    <a href="my_bookings.php" class="btn secondary" style="flex: 1; min-width: 200px; text-align: center; text-decoration: none;">
                        ← Keep Booking
                    </a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
