<?php
require_once 'mysql.php';

$error = '';

if (isset($_POST['submit'])){
    if(empty($_POST['name']) || empty($_POST['username']) || empty($_POST['email']) || empty($_POST['password']) || empty($_POST['invitation_code'])) {
        $error = 'All fields are required';
    } elseif (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email format';
    } else {
        $stmt = $connection->prepare("SELECT * FROM invitation_codes WHERE invitation_code = ? AND used = 0");
        $stmt->bind_param("s", $_POST['invitation_code']);
        $stmt->execute();
        if($stmt->get_result()->num_rows == 0) {
            $error = 'Invalid or already used invitation code';
        } else {
            $stmt = $connection->prepare("SELECT * FROM users WHERE email = ?");
            $stmt->bind_param("s", $_POST['email']);
            $stmt->execute();
            if($stmt->get_result()->num_rows > 0) {
                $error = 'Email already registered';
            } else {
                $stmt = $connection->prepare("SELECT * FROM users WHERE username = ?");
                $stmt->bind_param("s", $_POST['username']);
                $stmt->execute();
                if($stmt->get_result()->num_rows > 0) {
                    $error = 'Username already registered';
                } else {
                    $hashed_password = password_hash($_POST['password'], PASSWORD_DEFAULT);
                    $stmt = $connection->prepare("INSERT INTO users (name, username, email, password, created_at, updated_at) VALUES (?, ?, ?, ?, NOW(), NOW())");
                    $stmt->bind_param("ssss", $_POST['name'], $_POST['username'], $_POST['email'], $hashed_password);
                    $stmt->execute();
                    if($stmt->affected_rows > 0) {
                        $stmt = $connection->prepare("UPDATE invitation_codes SET used = 1 WHERE invitation_code = ?");
                        $stmt->bind_param("s", $_POST['invitation_code']);
                        $stmt->execute();
                        $connection->close();
                        header("Location: /msg.php?msg=" . urlencode("Registered successfully! Welcome aboard.") . "&type=success&goto=/login.php");
                        exit();
                    } else {
                        $error = 'Failed to register user';
                    }
                }
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
    <title>Register</title>
    <link rel="stylesheet" href="//static.paradowx2.sbs/style.css">
</head>
<body>
<div class="card">
    <h1>Register</h1>
    <?php if($error): ?>
    <div class="msg msg-error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    <form method="POST" action="/register.php" accept-charset="UTF-8" autocomplete="on" novalidate>
        <div class="field">
            <label>Full Name</label>
            <input name="name" type="text" placeholder="Mamad" value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>" required />
        </div>
        <div class="field">
            <label>Email</label>
            <input name="email" type="email" placeholder="mamad@gmail.com" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required />
        </div>
        <div class="field">
            <label>Username</label>
            <input name="username" type="text" placeholder="hajmamad" value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>" required />
        </div>
        <div class="field">
            <label>Password</label>
            <input name="password" type="password" placeholder="*******" required />
        </div>
        <div class="field">
            <label>Invitation Code</label>
            <input name="invitation_code" type="text" placeholder="xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx" required />
        </div>
        <div class="field">
            <button type="submit" name="submit">Register</button>
        </div>
    </form>
    <div class="page-links">
        Already have an account? <a href="/login.php">Login</a>
    </div>
</div>
</body>
</html>