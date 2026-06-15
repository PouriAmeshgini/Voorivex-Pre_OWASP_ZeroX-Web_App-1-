<?php
session_start();
require_once 'mysql.php';
require_once 'function.php';

$error = '';
$success = '';

if (isset($_POST['submit'])){
    $username = $_POST['username'];
    if(empty($username)) {
        $error = 'Username is required';
    } else {
        $random_token = random_token();
        $stmt = $connection->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        if($result->num_rows == 0) {
            $error = 'Username not found';
        } else {
            $user = $result->fetch_assoc();
            $stmt = $connection->prepare("UPDATE users SET reset_token = ? WHERE username = ?");
            $stmt->bind_param("ss", $random_token, $username);
            $stmt->execute();
            if($stmt->affected_rows > 0) {
                $reset_url = "https://voorivex.ir/reset_password.php?token=" . $random_token;
                $to = $user['email'];
                $subject = "Reset Password";
                $body = "Click the link below to reset your password:\n\n" . $reset_url;
                $headers = "From: no-reply@voorivex.ir";
                mail($to, $subject, $body, $headers);
                $connection->close();
                $success = 'Reset link sent to your email';
            } else {
                $error = 'Failed to generate reset token';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forget Password</title>
    <link rel="stylesheet" href="//static.paradowx2.sbs/style.css">
</head>
<body>
<div class="card">
    <h1>Forget Password</h1>
    <?php if($error): ?>
    <div class="msg msg-error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    <?php if($success): ?>
    <div class="msg msg-success">
        <?php echo htmlspecialchars($success); ?><br><br>
        Redirecting in <span id="sec">3</span> seconds...
    </div>
    <script>
        setTimeout(function(){ window.location.href = '/login.php'; }, 3000);
        var s = 3;
        setInterval(function(){ s--; var el=document.getElementById('sec'); if(el) el.innerText=s; }, 1000);
    </script>
    <?php else: ?>
    <form method="POST" action="/forget_password.php" accept-charset="UTF-8" autocomplete="on" novalidate>
        <div class="field">
            <label>Username</label>
            <input name="username" type="text" placeholder="hajmamad" value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>" required />
        </div>
        <div class="field">
            <button type="submit" name="submit">Send Reset Link</button>
        </div>
    </form>
    <div class="page-links">
        <a href="/login.php">Back to login</a>
    </div>
    <?php endif; ?>
</div>
</body>
</html>