<?php
session_start();
require_once 'mysql.php';

$user_id = isset($_GET['user_id']) ? (int)$_GET['user_id'] : 0;

if ($user_id <= 0) {
    header('Location: /msg.php?msg=' . urlencode('Invalid profile') . '&type=error&goto=/index.php');
    exit();
}

$user_info = null;

$api_url = 'http://127.0.0.1:5000/api/user/' . $user_id;
$context = stream_context_create([
    'http' => [
        'timeout' => 5,
        'ignore_errors' => true,
    ],
]);
$api_response = @file_get_contents($api_url, false, $context);

if ($api_response !== false) {
    $api_data = json_decode($api_response, true);
    if (is_array($api_data) && !isset($api_data['error'])) {
        $user_info = $api_data;
    }
}

if (!$user_info) {
    $stmt = $connection->prepare("SELECT id, name, username, profile_picture, bio, created_at FROM users WHERE id = ?");
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $user_info = $stmt->get_result()->fetch_assoc();
}

if (!$user_info) {
    header('Location: /msg.php?msg=' . urlencode('User not found') . '&type=error&goto=/index.php');
    exit();
}

$name = $user_info['name'] ?? '';
$username = $user_info['username'] ?? '';
$bio = $user_info['bio'] ?? '';

$profilePicture = !empty($user_info['profile_picture']) ? $user_info['profile_picture'] : 'default.png';
$profilePic = '//static.paradowx2.sbs/user_profiles/' . rawurlencode($profilePicture);
$joined = !empty($user_info['created_at']) ? date('M Y', strtotime($user_info['created_at'])) : '';

$tweets = [];
$stmt = $connection->prepare("SELECT id, content, created_at FROM tweets WHERE user_id = ? ORDER BY created_at DESC");
$stmt->bind_param('i', $user_id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $tweets[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($name); ?> — Profile</title>
    <link rel="stylesheet" href="//static.paradowx2.sbs/style.css">
</head>
<body>

<div class="public-profile-wrap">
    <div class="public-profile-card">
        <div class="public-profile-avatar-wrap">
            <img src="<?php echo $profilePic; ?>" class="public-profile-avatar" alt="Profile picture">
        </div>

        <div class="public-profile-info">
            <h1 class="public-profile-name"><?php echo htmlspecialchars($name); ?></h1>
            <p class="public-profile-username">@<?php echo htmlspecialchars($username); ?></p>
            <div class="public-profile-meta-row">
                <p class="public-profile-meta">Joined <?php echo htmlspecialchars($joined); ?></p>
            </div>

            <?php if (!empty($bio)): ?>
            <blockquote class="public-profile-bio"><?php echo htmlspecialchars($bio); ?></blockquote>
            <?php else: ?>
            <p class="public-profile-bio-empty">No bio yet.</p>
            <?php endif; ?>
        </div>
    </div>

    <section class="profile-tweets">
        <h2 class="profile-tweets-title">Tweets</h2>

        <?php if (empty($tweets)): ?>
        <p class="profile-tweets-empty">No tweets yet.</p>
        <?php else: ?>
        <div class="profile-tweets-list">
            <?php foreach ($tweets as $tweet): ?>
            <article class="profile-tweet">
                <div class="profile-tweet-main">
                    <p class="profile-tweet-content"><?php echo htmlspecialchars($tweet['content']); ?></p>
                    <time class="profile-tweet-date" datetime="<?php echo htmlspecialchars($tweet['created_at']); ?>">
                        <?php echo htmlspecialchars(date('M j, Y · g:i A', strtotime($tweet['created_at']))); ?>
                    </time>
                </div>
                <a href="/delete.php?post_id=<?php echo (int)$tweet['id']; ?>"
                   class="profile-tweet-delete"
                   target="_blank"
                   rel="noopener noreferrer"
                   title="Delete tweet">&times;</a>
            </article>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </section>

    <a href="/index.php" class="public-profile-back">&larr; Back to home</a>
</div>

</body>
</html>
