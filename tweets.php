<?php
require_once 'mysql.php';

$all_tweets = [];
$stmt = $connection->prepare(
    'SELECT t.id, t.content, t.created_at, u.id AS user_id, u.name, u.username, u.profile_picture
     FROM tweets t
     INNER JOIN users u ON u.id = t.user_id
     ORDER BY t.created_at DESC'
);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $pic = !empty($row['profile_picture']) ? $row['profile_picture'] : 'default.png';
    $row['profile_picture_url'] = '//static.paradowx2.sbs/user_profiles/' . rawurlencode($pic);
    $row['created_human'] = date('M j, Y, g:i A', strtotime($row['created_at']));
    $all_tweets[] = $row;
}

header('Content-Type: application/json');
echo json_encode($all_tweets);
