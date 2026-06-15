<?php
session_start();

if (!isset($_SESSION['login'])) {
    header('Location: /login.php');
    exit();
}

require_once 'mysql.php';

if (!isset($_POST['submit'])) {
    header('Location: /panel.php');
    exit();
}

$user_id = (int)$_SESSION['user_id'];
$name = trim($_POST['name']);
$bio = trim($_POST['bio'] ?? '');
$password = $_POST['password'] ?? '';
$current_profile_picture = $_POST['current_profile_picture'] ?? 'default.png';

if ($name === '') {
    header('Location: /msg.php?msg=' . urlencode('Name is required') . '&type=error&goto=/panel.php');
    exit();
}

$profile_picture = $current_profile_picture ?: 'default.png';

if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] !== UPLOAD_ERR_NO_FILE) {
    if ($_FILES['profile_picture']['error'] !== UPLOAD_ERR_OK) {
        header('Location: /msg.php?msg=' . urlencode('Failed to upload profile picture') . '&type=error&goto=/panel.php');
        exit();
    }

    $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
    $original_name = $_FILES['profile_picture']['name'];
    $extension = strtolower(pathinfo($original_name, PATHINFO_EXTENSION));

    if (!in_array($extension, $allowed_extensions, true)) {
        header('Location: /msg.php?msg=' . urlencode('Invalid file type') . '&type=error&goto=/panel.php');
        exit();
    }

    if ($_FILES['profile_picture']['size'] > 300 * 1024) {
        header('Location: /msg.php?msg=' . urlencode('Max size 300KB') . '&type=error&goto=/panel.php');
        exit();
    }

    $upload_dir = '/var/www/static/user_profiles/';
    if (!is_dir($upload_dir) && !mkdir($upload_dir, 0777, true)) {
        header('Location: /msg.php?msg=' . urlencode('Unable to prepare upload directory') . '&type=error&goto=/panel.php');
        exit();
    }

    if (!is_writable($upload_dir)) {
        @chmod($upload_dir, 0777);
    }

    if (!is_writable($upload_dir)) {
        header('Location: /msg.php?msg=' . urlencode('Upload folder is not writable') . '&type=error&goto=/panel.php');
        exit();
    }

    $stored_extension = $extension === 'jpeg' ? 'jpg' : $extension;
    $new_filename = 'user_' . $user_id . '_' . time() . '.' . $stored_extension;
    $destination = $upload_dir . $new_filename;

    if (!move_uploaded_file($_FILES['profile_picture']['tmp_name'], $destination)) {
        header('Location: /msg.php?msg=' . urlencode('Failed to save uploaded file') . '&type=error&goto=/panel.php');
        exit();
    }

    if ($current_profile_picture !== 'default.png' && $current_profile_picture !== '') {
        $old_path = $upload_dir . $current_profile_picture;
        if (is_file($old_path)) {
            unlink($old_path);
        }
    }

    $profile_picture = $new_filename;
}

if ($password !== '') {
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $connection->prepare("UPDATE users SET name = ?, bio = ?, password = ?, profile_picture = ?, updated_at = NOW() WHERE id = ?");
    $stmt->bind_param('ssssi', $name, $bio, $hashed_password, $profile_picture, $user_id);
} else {
    $stmt = $connection->prepare("UPDATE users SET name = ?, bio = ?, profile_picture = ?, updated_at = NOW() WHERE id = ?");
    $stmt->bind_param('sssi', $name, $bio, $profile_picture, $user_id);
}

if (!$stmt->execute()) {
    header('Location: /msg.php?msg=' . urlencode('Failed to update profile') . '&type=error&goto=/panel.php');
    exit();
}

header('Location: /msg.php?msg=' . urlencode('Profile updated') . '&type=success&goto=/panel.php');
exit();
