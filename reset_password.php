<?php
session_start();
require_once 'mysql.php';
require_once 'function.php';

if(isset($_POST['submit'])){
    $token = $_POST['token'];
    $password = $_POST['password'];
    if(empty($password)) {
        header("Location: /msg.php?msg=" . urlencode("Password is required") . "&type=error&goto=/login.php");
        exit();
    }
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $connection->prepare("UPDATE users SET password = ?, reset_token = NULL WHERE reset_token = ?");
    $stmt->bind_param("ss", $hashed_password, $token);
    $stmt->execute();
    if($stmt->affected_rows > 0) {
        $connection->close();
        header("Location: /msg.php?msg=" . urlencode("Password updated successfully!") . "&type=success&goto=/login.php");
        exit();
    } else {
        $connection->close();
        header("Location: /msg.php?msg=" . urlencode("Failed to update password") . "&type=error&goto=/login.php");
        exit();
    }
}

if(isset($_GET['token'])){
    $token = $_GET['token'];
    $stmt = $connection->prepare("SELECT * FROM users WHERE reset_token = ? AND reset_token IS NOT NULL");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();
    if($result->num_rows == 0) {
        header("Location: /msg.php?msg=" . urlencode("Invalid or expired token") . "&type=error&goto=/forget_password.php");
        exit();
    }
    $user = $result->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
    <link rel="stylesheet" href="//static.paradowx2.sbs/style.css">
</head>
<body>
<div class="card">
    <h1>Reset Password</h1>
    <p class="text-muted mt-12">Resetting for: <strong><?php echo htmlspecialchars($user['username']); ?></strong></p>
    <br>
    <form method="POST" action="/reset_password.php" accept-charset="UTF-8" autocomplete="off" novalidate>
        <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>" />
        <div class="field">
            <label>New Password</label>
            <input name="password" type="password" placeholder="*********" required />
        </div>
        <div class="field">
            <button type="submit" name="submit">Reset Password</button>
        </div>
    </form>
    <div class="page-links">
        <a href="/login.php">Back to login</a>
    </div>
</div>
</body>
</html>
<?php
} else {
    header("Location: /msg.php?msg=" . urlencode("No token provided") . "&type=error&goto=/forget_password.php");
    exit();
}
?>