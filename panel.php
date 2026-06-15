<?php
session_start();
if (!isset($_SESSION['login'])) {
    header('Location: /login.php');
    exit();
}
require_once 'mysql.php';

if (isset($_GET['logout'])) {
    session_unset();
    session_destroy();
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params['path'], $params['domain'],
            $params['secure'], $params['httponly']
        );
    }


    header('Location: /msg.php?msg=' . urlencode('Logged out successfully') . '&type=success&goto=/login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$stmt = $connection->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

$currentProfilePicture = !empty($user['profile_picture']) ? $user['profile_picture'] : 'default.png';
$profilePic = '//static.paradowx2.sbs/user_profiles/' . rawurlencode($currentProfilePicture);

$bio = $user['bio'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel</title>
    <link rel="stylesheet" href="//static.paradowx2.sbs/style.css">
</head>
<body>

<div class="panel-wrap">
    <div class="panel-body">

        <div class="panel-header">
            <span class="logo">Panel</span>
            <div class="panel-header-actions">
                <a href="?logout=1" class="logout-link">Logout</a>
                <a href="/profile.php?user_id=<?php echo (int)$user['id']; ?>"
                   class="public-profile-link"
                   target="_blank"
                   rel="noopener noreferrer">
                    <span class="public-profile-link-icon">↗</span>
                    View Public Profile
                </a>
            </div>
        </div>

        <div class="profile-card">
            <img src="<?php echo $profilePic; ?>" class="avatar-circle" id="avatar-preview" alt="Profile picture">
            <div class="profile-card-info">
                <p class="profile-card-name"><?php echo htmlspecialchars($user['name']); ?></p>
                <p class="profile-card-username">@<?php echo htmlspecialchars($user['username']); ?></p>
                <?php if (!empty($user['bio'])): ?>
                <blockquote class="profile-card-bio"><?php echo htmlspecialchars($user['bio']); ?></blockquote>
                <?php endif; ?>
            </div>
        </div>

        <p class="panel-greeting">You are logged in.</p>
        <hr class="panel-divider">

        <form action="update_user.php" method="POST" enctype="multipart/form-data" class="profile-edit-form">
            <input type="hidden" name="current_profile_picture"
                   value="<?php echo htmlspecialchars($currentProfilePicture); ?>">

            <div class="field">
                <label>Username</label>
                <input type="text" value="<?php echo htmlspecialchars($user['username']); ?>" disabled />
            </div>
            <div class="field">
                <label>Name</label>
                <input type="text" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" required />
            </div>
            <div class="field">
                <label>Email</label>
                <input type="email" value="<?php echo htmlspecialchars($user['email']); ?>" disabled />
            </div>
            <div class="field field-bio">
                <label for="bio">Bio</label>
                <div class="bio-input-wrap">
                    <textarea name="bio" id="bio" rows="6" maxlength="500" placeholder="Tell us a little about yourself..."><?php echo htmlspecialchars($user['bio'] ?? ''); ?></textarea>
                </div>
                <div class="bio-field-footer">
                    <span class="bio-char-count" id="bio-count"><?php echo strlen($user['bio'] ?? ''); ?>/500</span>
                </div>
            </div>

            <div class="field">
                <label>Profile Picture</label>
                <div class="file-row">
                    <input type="file" name="profile_picture" id="avatar-file"
                           accept="image/jpeg,image/png,image/gif" onchange="onFileSelected(this)">
                </div>
                <p class="field-hint" id="file-hint">PNG, JPG, or GIF up to 300KB.</p>
            </div>

            <hr class="panel-divider">

            <div class="field">
                <label>Change Password</label>
                <input type="password" name="password" placeholder="Leave blank to keep current" />
            </div>

            <button type="submit" name="submit">Save Changes</button>
        </form>

    </div>
</div>

<script>
var defaultHint = 'PNG, JPG, or GIF up to 300KB.';
var bioInput = document.getElementById('bio');
var bioCount = document.getElementById('bio-count');

function updateBioCount() {
    if (!bioInput || !bioCount) return;
    bioCount.textContent = bioInput.value.length + '/500';
}

if (bioInput) {
    bioInput.addEventListener('input', updateBioCount);
    updateBioCount();
}

function onFileSelected(input) {
    var hint = document.getElementById('file-hint');
    var file = input.files[0];

    if (!file) {
        hint.textContent = defaultHint;
        return;
    }

    if (file.size > 300 * 1024) {
        alert('File too large — max 300KB');
        input.value = '';
        hint.textContent = defaultHint;
        return;
    }

    hint.textContent = 'Selected: ' + file.name + ' — click Save Changes to apply.';
}
</script>

</body>
</html>
