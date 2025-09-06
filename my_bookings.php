<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Get user's bookings
$bookings_query = "SELECT b.*, r.transport_type, r.origin, r.destination, r.departure_time, r.price, p.payment_method, p.status as payment_status
                   FROM Bookings b 
                   JOIN Routes r ON b.route_id = r.route_id 
                   LEFT JOIN Payments p ON b.booking_id = p.booking_id
                   WHERE b.user_id = ? 
                   ORDER BY b.booking_date DESC";

$stmt = $conn->prepare($bookings_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Bookings - Swift Book</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <h1>📋 My Bookings</h1>
        
        <div class="actions">
            <a href="index.php" class="btn secondary">← Back to Dashboard</a>
            <a href="book_ticket.php" class="btn">🎫 Book New Ticket</a>
        </div>
        
        <?php if ($result->num_rows > 0): ?>
            <div class="bookings-container">
                <?php while ($booking = $result->fetch_assoc()): ?>
                    <div class="route-card">
                        <div class="route-header">
                            <h3>Booking #<?= $booking['booking_id'] ?></h3>
                            <span class="route-type <?= $booking['status'] == 'Confirmed' ? 'status-confirmed' : 'status-cancelled' ?>">
                                <?= $booking['status'] ?>
                            </span>
                        </div>
                        
                        <div class="route-details">
                            <div class="detail-item">
                                <div class="detail-label">Transport</div>
                                <div class="detail-value"><?= $booking['transport_type'] ?></div>
                            </div>
                            
                            <div class="detail-item">
                                <div class="detail-label">Route</div>
                                <div class="detail-value"><?= htmlspecialchars($booking['origin']) ?> → <?= htmlspecialchars($booking['destination']) ?></div>
                            </div>
                            
                            <div class="detail-item">
                                <div class="detail-label">Departure</div>
                                <div class="detail-value"><?= date('h:i A', strtotime($booking['departure_time'])) ?></div>
                            </div>
                            
                            <div class="detail-item">
                                <div class="detail-label">Seat</div>
                                <div class="detail-value">#<?= $booking['seat_number'] ?></div>
                            </div>
                            
                            <div class="detail-item">
                                <div class="detail-label">Price</div>
                                <div class="detail-value">৳<?= number_format($booking['price'], 2) ?></div>
                            </div>
                            
                            <div class="detail-item">
                                <div class="detail-label">Booked On</div>
                                <div class="detail-value"><?= date('M d, Y', strtotime($booking['booking_date'])) ?></div>
                            </div>
                            
                            <div class="detail-item">
                                <div class="detail-label">Payment</div>
                                <div class="detail-value">
                                    <?= $booking['payment_method'] ?? 'Cash' ?><br>
                                    <small style="color: <?= $booking['payment_status'] == 'Completed' ? '#4CAF50' : '#f44336' ?>;">
                                        <?= $booking['payment_status'] ?? 'Completed' ?>
                                    </small>
                                </div>
                            </div>
                            
                            <div class="detail-item">
                                <div class="detail-label">Action</div>
                                <div class="detail-value">
                                    <?php if ($booking['status'] == 'Confirmed'): ?>
                                        <a href="cancel_booking.php?id=<?= $booking['booking_id'] ?>" 
                                           class="btn danger"
                                           onclick="return confirm('Are you sure you want to cancel this booking?');">
                                           ❌ Cancel
                                        </a>
                                    <?php else: ?>
                                        <span style="color: #666;">Cancelled</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="route-card">
                <div class="center">
                    <h3>No bookings found</h3>
                    <p>You haven't made any bookings yet.</p>
                    <a href="book_ticket.php" class="btn">🎫 Book Your First Ticket</a>
                </div>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
