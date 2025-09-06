<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['user_id']) || !$_SESSION['is_admin']) {
    header('Location: index.php');
    exit();
}

$message = '';

// Handle user actions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'];
    $user_id = $_POST['user_id'];
    
    if ($action == 'toggle_admin') {
        $stmt = $conn->prepare("UPDATE Users SET is_admin = NOT is_admin WHERE user_id = ?");
        $stmt->bind_param("i", $user_id);
        if ($stmt->execute()) {
            $message = "User admin status updated successfully!";
        }
    } elseif ($action == 'delete_user') {
        $stmt = $conn->prepare("DELETE FROM Users WHERE user_id = ? AND is_admin = FALSE");
        $stmt->bind_param("i", $user_id);
        if ($stmt->execute()) {
            $message = "User deleted successfully!";
        }
    }
}

// Get all users
$users_query = "SELECT * FROM Users ORDER BY is_admin DESC, username ASC";
$users_result = $conn->query($users_query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users - Swift Book</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <h1>👥 Manage Users</h1>
        
        <div class="actions">
            <a href="index.php" class="btn secondary">← Back to Dashboard</a>
        </div>
        
        <?php if ($message): ?>
            <div class="success"><?= $message ?></div>
        <?php endif; ?>
        
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Contact</th>
                        <th>Admin</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($user = $users_result->fetch_assoc()): ?>
                        <tr>
                            <td><?= $user['user_id'] ?></td>
                            <td><?= htmlspecialchars($user['username']) ?></td>
                            <td><?= htmlspecialchars($user['email']) ?></td>
                            <td><?= htmlspecialchars($user['contact']) ?></td>
                            <td>
                                <span class="route-type <?= $user['is_admin'] ? 'status-confirmed' : '' ?>">
                                    <?= $user['is_admin'] ? 'Admin' : 'User' ?>
                                </span>
                            </td>
                            <td>
                                <div class="table-actions">
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="user_id" value="<?= $user['user_id'] ?>">
                                        <input type="hidden" name="action" value="toggle_admin">
                                        <button type="submit" class="btn" style="font-size: 12px; padding: 4px 8px;">
                                            <?= $user['is_admin'] ? 'Remove Admin' : 'Make Admin' ?>
                                        </button>
                                    </form>
                                    <?php if (!$user['is_admin']): ?>
                                        <form method="POST" style="display: inline;" onsubmit="return confirm('Delete this user?');">
                                            <input type="hidden" name="user_id" value="<?= $user['user_id'] ?>">
                                            <input type="hidden" name="action" value="delete_user">
                                            <button type="submit" class="btn danger" style="font-size: 12px; padding: 4px 8px;">
                                                Delete
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
