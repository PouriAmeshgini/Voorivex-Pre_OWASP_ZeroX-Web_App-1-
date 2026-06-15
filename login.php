<?php
session_start();
function get_ip_address(){ return $_SERVER['REMOTE_ADDR']; }
require_once 'mysql.php';

$error = '';
$success = '';

if (isset($_POST['submit'])){
    $username = $_POST['username'];
    $password = $_POST['password'];
    $ip_address = get_ip_address();
    $user_agent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
    $referer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';

    if(empty($username) || empty($password)) {
        $error = 'All fields are required';
    } else {
        $stmt = $connection->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        if($result->num_rows == 0) {
            $login_status = 0;
            $stmt2 = $connection->prepare("INSERT INTO login_logs (ip_address, user_agent, referer, login_status, username, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
            $stmt2->bind_param("sssis", $ip_address, $user_agent, $referer, $login_status, $username);
            $stmt2->execute();
            $error = 'Username not found';
        } else {
            $user = $result->fetch_assoc();
            if(password_verify($password, $user['password'])) {
                $_SESSION['login'] = true;
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['name'] = $user['name'];
                $login_status = 1;
                $stmt2 = $connection->prepare("INSERT INTO login_logs (ip_address, user_agent, referer, login_status, username, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
                $stmt2->bind_param("sssis", $ip_address, $user_agent, $referer, $login_status, $username);
                $stmt2->execute();
                $connection->close();
                $success = 'Login successful! Welcome back, ' . htmlspecialchars($user['username']);
            } else {
                $login_status = 0;
                $stmt2 = $connection->prepare("INSERT INTO login_logs (ip_address, user_agent, referer, login_status, username, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
                $stmt2->bind_param("sssis", $ip_address, $user_agent, $referer, $login_status, $username);
                $stmt2->execute();
                $error = 'Wrong password';
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
    <title>Login</title>
    <link rel="stylesheet" href="//static.paradowx2.sbs/style.css">
</head>
<body>
<div class="card">
    <h1>Login</h1>

    <?php if($error): ?>
    <div class="msg msg-error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <?php if($success): ?>
    <div class="msg msg-success">
        <?php echo $success; ?><br><br>
        Redirecting in <span id="sec">3</span> seconds...
    </div>
    <script>
        setTimeout(function(){ window.location.href = '/panel.php'; }, 3000);
        var s = 3;
        setInterval(function(){
            s--;
            var el = document.getElementById('sec');
            if(el) el.innerText = s;
        }, 1000);
    </script>
    <?php endif; ?>

    <?php if(!$success): ?>
    <form method="POST" action="/login.php" accept-charset="UTF-8" autocomplete="on" novalidate>
        <div class="field">
            <label>Username</label>
            <input name="username" type="text" placeholder="hajmamad" value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>" required />
        </div>
        <div class="field">
            <label>Password</label>
            <input name="password" type="password" placeholder="*****" required />
        </div>
        <div class="field">
            <button type="submit" name="submit">Login</button>
        </div>
    </form>
    <div class="page-links">
        Don't have an account? <a href="/register.php">Register</a><br>
        <a href="/forget_password.php">Forgot password?</a>
    </div>
    <?php endif; ?>
</div>
</body>
</html>