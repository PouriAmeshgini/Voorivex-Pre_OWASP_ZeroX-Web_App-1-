<?php

session_start();
require_once 'mysql.php';

if (!isset($_SESSION['login'])) {
    header('Location: msg.php?msg=You are not logged in&type=error&goto=login.php');
    exit();
}

if (isset($_GET['post_id'])) {
    $post_id = $_GET['post_id'];
    $user_id = $_SESSION['user_id'];
    //$stmt = $connection->prepare("DELETE FROM tweets WHERE id = ?");
    $stmt = $connection->prepare('DELETE FROM tweets WHERE id = ? AND user_id = ?');
    $stmt->bind_param("ii", $post_id, $_SESSION['user_id']);
    $stmt->execute();
    header('Location: msg.php?msg=Tweet deleted successfully&type=success&goto=profile.php?user_id=' . urlencode($user_id));
    exit();
} else {
    header('Location: msg.php?msg=Tweet not found&type=error&goto=profile.php?user_id=' . urlencode($_SESSION['user_id']));
    exit();
}

?>
