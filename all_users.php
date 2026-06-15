<?php
session_start();
require_once 'mysql.php';

$users = [];
$stmt = $connection->prepare('SELECT id, name, username, email, created_at FROM users ORDER BY created_at DESC');
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $users[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Users</title>
    <link rel="stylesheet" href="//static.paradowx2.sbs/style.css">
</head>
<body>

<div class="card all-users-card">
    <h1>All Users</h1>

    <?php if (empty($users)): ?>
    <p class="all-users-empty">No users found.</p>
    <?php else: ?>
    <div class="all-users-table-wrap">
        <table class="all-users-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Created At</th>
                    <th>Profile</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                <tr>
                    <td><?php echo (int)$user['id']; ?></td>
                    <td><?php echo htmlspecialchars($user['name']); ?></td>
                    <td>@<?php echo htmlspecialchars($user['username']); ?></td>
                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                    <td><?php echo htmlspecialchars($user['created_at']); ?></td>
                    <td>
                        <a href="/profile.php?user_id=<?php echo (int)$user['id']; ?>">View</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>

    <a href="/index.php" class="btn-secondary mt-20">Back to home</a>
</div>

</body>
</html>
