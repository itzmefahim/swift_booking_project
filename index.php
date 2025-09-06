<?php
session_start();
include 'db_connect.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$is_admin = $_SESSION['is_admin'];

// Get statistics
$stats = [];
$stats['total_routes'] = $conn->query("SELECT COUNT(*) as count FROM Routes WHERE status = 'Active'")->fetch_assoc()['count'];
$stats['total_bookings'] = $conn->query("SELECT COUNT(*) as count FROM Bookings WHERE status = 'Confirmed'")->fetch_assoc()['count'];
$stats['total_users'] = $conn->query("SELECT COUNT(*) as count FROM Users WHERE is_admin = FALSE")->fetch_assoc()['count'];
$stats['available_seats'] = $conn->query("SELECT SUM(available_seats) as count FROM Routes WHERE status = 'Active'")->fetch_assoc()['count'];

// Get recent bookings for display
if ($is_admin) {
    $bookings_query = "SELECT b.*, u.username, r.transport_type, r.origin, r.destination, r.departure_time, r.price 
                       FROM Bookings b 
                       JOIN Users u ON b.user_id = u.user_id 
                       JOIN Routes r ON b.route_id = r.route_id 
                       WHERE b.status = 'Confirmed' 
                       ORDER BY b.booking_date DESC LIMIT 10";
} else {
    $bookings_query = "SELECT b.*, r.transport_type, r.origin, r.destination, r.departure_time, r.price 
                       FROM Bookings b 
                       JOIN Routes r ON b.route_id = r.route_id 
                       WHERE b.user_id = $user_id AND b.status = 'Confirmed' 
                       ORDER BY b.booking_date DESC";
}
$bookings_result = $conn->query($bookings_query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Swift Book - Online Ticket Reservation System</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <h1>🚌 Swift Book - Ticket Reservation System</h1>
        
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-number"><?= $stats['total_routes'] ?></div>
                <div class="stat-label">Active Routes</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?= $stats['total_bookings'] ?></div>
                <div class="stat-label">Total Bookings</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?= $stats['total_users'] ?></div>
                <div class="stat-label">Registered Users</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?= $stats['available_seats'] ?></div>
                <div class="stat-label">Available Seats</div>
            </div>
        </div>
        
        <div class="actions">
            <?php if ($is_admin): ?>
                <a href="add_route.php" class="btn">➕ Add Route</a>
                <a href="manage_users.php" class="btn">👥 Manage Users</a>
            <?php endif; ?>
            <a href="view_routes.php" class="btn">🚌 View Routes</a>
            <a href="book_ticket.php" class="btn">🎫 Book Ticket</a>
            <a href="my_bookings.php" class="btn">📋 My Bookings</a>
            <?php if ($is_admin): ?>
                <a href="db_inspector.php" class="btn">🗄️ DB Inspector</a>
            <?php endif; ?>
            <a href="logout.php" class="btn secondary">🚪 Logout</a>
        </div>

        <h2><?= $is_admin ? 'Recent Bookings (All Users)' : 'My Recent Bookings' ?></h2>
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Booking ID</th>
                        <?php if ($is_admin): ?><th>User</th><?php endif; ?>
                        <th>Transport</th>
                        <th>Route</th>
                        <th>Time</th>
                        <th>Seat</th>
                        <th>Price</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($bookings_result->num_rows > 0): ?>
                        <?php while ($row = $bookings_result->fetch_assoc()): ?>
                            <tr>
                                <td><?= $row['booking_id'] ?></td>
                                <?php if ($is_admin): ?><td><?= htmlspecialchars($row['username']) ?></td><?php endif; ?>
                                <td><?= $row['transport_type'] ?></td>
                                <td><?= htmlspecialchars($row['origin']) ?> → <?= htmlspecialchars($row['destination']) ?></td>
                                <td><?= $row['departure_time'] ?></td>
                                <td><?= $row['seat_number'] ?></td>
                                <td>৳<?= number_format($row['price'], 2) ?></td>
                                <td><?= date('M d, Y', strtotime($row['booking_date'])) ?></td>
                                <td class="center">
                                    <div class="table-actions">
                                        <a href="cancel_booking.php?id=<?= $row['booking_id'] ?>" class="delete-link"
                                           onclick="return confirm('Cancel this booking?');">❌ Cancel</a>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="<?= $is_admin ? '9' : '8' ?>" class="center">No bookings found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
