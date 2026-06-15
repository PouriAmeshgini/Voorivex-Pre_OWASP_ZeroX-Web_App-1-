<?php
session_start();
require_once 'mysql.php';

$tweet_error = '';
$tweet_success = '';
$current_user = null;

if (isset($_SESSION['login'])) {
    $session_user_id = (int)$_SESSION['user_id'];
    $stmt = $connection->prepare('SELECT id, name, username, profile_picture FROM users WHERE id = ?');
    $stmt->bind_param('i', $session_user_id);
    $stmt->execute();
    $current_user = $stmt->get_result()->fetch_assoc();
}

if (isset($_POST['tweet_submit'])) {
    if (!isset($_SESSION['login'])) {
        $tweet_error = 'You must be logged in to post a tweet.';
    } else {
        $content = trim($_POST['content'] ?? '');

        if ($content === '') {
            $tweet_error = 'Tweet cannot be empty.';
        } elseif (strlen($content) > 500) {
            $tweet_error = 'Tweet must be 500 characters or less.';
        } else {
            $user_id = (int)$_SESSION['user_id'];
            $stmt = $connection->prepare('INSERT INTO tweets (user_id, content) VALUES (?, ?)');
            $stmt->bind_param('is', $user_id, $content);

            if ($stmt->execute()) {
                $tweet_success = 'Tweet posted successfully.';
                $_POST['content'] = '';
            } else {
                $tweet_error = 'Failed to post tweet.';
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
    <title>VooriMed</title>
    <link rel="stylesheet" href="//static.paradowx2.sbs/style.css?v=2">
</head>
<body>

<div class="home-layout">
    <main class="home-main">
        <div class="card home-composer-card">
            <div class="home-welcome-block">
                <h1>> VooriMed</h1>
                <p class="subtitle">// welcome to the platform</p>
            </div>

            <div class="home-composer-divider"></div>

            <div class="home-tweet-block">
            <h2 class="tweet-card-title">Post a Tweet</h2>

            <?php if ($tweet_error): ?>
            <div class="msg msg-error"><?php echo htmlspecialchars($tweet_error); ?></div>
            <?php endif; ?>

            <?php if ($tweet_success): ?>
            <div class="msg msg-success"><?php echo htmlspecialchars($tweet_success); ?></div>
            <?php endif; ?>

            <?php if (isset($_SESSION['login']) && $current_user): ?>
            <?php
                $posterPic = !empty($current_user['profile_picture']) ? $current_user['profile_picture'] : 'default.png';
                $posterAvatar = '//static.paradowx2.sbs/user_profiles/' . rawurlencode($posterPic);
            ?>
            <div class="tweet-poster">
                <img src="<?php echo $posterAvatar; ?>" class="tweet-poster-avatar" alt="Your profile picture">
                <div class="tweet-poster-info">
                    <p class="tweet-poster-label">Posting as</p>
                    <p class="tweet-poster-name"><?php echo htmlspecialchars($current_user['name']); ?></p>
                    <p class="tweet-poster-username">@<?php echo htmlspecialchars($current_user['username']); ?></p>
                    <?php if (!empty($current_user['bio'])): ?>
                    <p class="tweet-poster-bio"><?php echo htmlspecialchars($current_user['bio']); ?></p>
                    <?php endif; ?>
                </div>
            </div>

            <form method="POST" action="/index.php" class="tweet-form tweet-form-compact">
                <div class="field field-bio field-tweet">
                    <label for="tweet-content">What's happening?</label>
                    <div class="bio-input-wrap tweet-input-wrap">
                        <textarea name="content" id="tweet-content" rows="2" maxlength="500"
                                  placeholder="Write your tweet..."><?php echo htmlspecialchars($_POST['content'] ?? ''); ?></textarea>
                    </div>
                    <div class="bio-field-footer">
                        <span class="bio-char-count" id="tweet-count">0/500</span>
                    </div>
                </div>
                <button type="submit" name="tweet_submit" class="tweet-submit-btn">Post Tweet</button>
            </form>
            <?php else: ?>
            <p class="tweet-login-hint">Please <a href="/login.php">login</a> to post a tweet.</p>
            <?php endif; ?>
            </div>
        </div>

        <section class="card home-tweets-feed">
            <h2 class="home-tweets-title">All Tweets</h2>
            <div class="home-tweets-list"></div>
        </section>
    </main>

    <aside class="home-sidebar">
        <div class="nav-links">
            <a href="/login.php" class="nav-btn nav-btn-primary">Login</a>
            <a href="/register.php" class="nav-btn nav-btn-secondary">Register</a>
            <hr class="divider">
            <?php if (isset($_SESSION['login'])): ?>
                <a href="/panel.php" class="nav-btn nav-btn-panel">Go to Panel &rarr;</a>
            <?php else: ?>
                <a href="/panel.php" class="nav-btn nav-btn-panel">Panel (login required)</a>
            <?php endif; ?>
            <a href="/all_users.php" class="nav-btn nav-btn-secondary">All Users</a>
        </div>
    </aside>
</div>

<?php if (isset($_SESSION['login'])): ?>
<script>
var tweetInput = document.getElementById('tweet-content');
var tweetCount = document.getElementById('tweet-count');

function updateTweetCount() {
    if (!tweetInput || !tweetCount) return;
    tweetCount.textContent = tweetInput.value.length + '/500';
}

if (tweetInput) {
    tweetInput.addEventListener('input', updateTweetCount);
    updateTweetCount();
}
</script>
<?php endif; ?>

<script>
xhr = new XMLHttpRequest();
xhr.open('GET', 'tweets.php', true);
xhr.onreadystatechange = function() {
    if (xhr.readyState === 4) {
        if (xhr.status === 200) {
            data = JSON.parse(xhr.responseText);
            const tweetList = document.getElementsByClassName('home-tweets-list')[0];
            if (tweetList) {
                for (let i = 0; i < data.length; i++) {
                    const tweet = data[i];
                    const tweetCard = document.createElement('article');
                    tweetCard.className = 'home-tweet';
                    tweetCard.innerHTML = `
                        <a href="profile.php?user_id=${tweet.user_id}" class="home-tweet-avatar-link">
                            <img src="${tweet.profile_picture_url}" class="home-tweet-avatar" alt="">
                        </a>
                        <div class="home-tweet-body">
                            <div class="home-tweet-header">
                                <a href="profile.php?user_id=${tweet.user_id}" class="home-tweet-name">${tweet.name}</a>
                                <span class="home-tweet-username">@${tweet.username}</span>
                                <time class="home-tweet-date">${tweet.created_human || ''}</time>
                            </div>
                            <p class="home-tweet-content">${tweet.content}</p>
                        </div>
                    `;
                    tweetList.appendChild(tweetCard);
                }
            }
        }
    }
};
xhr.send();
</script>

</body>
</html>

