<?php
session_start();
require_once 'mysql.php';
require_once 'function.php';

$msg  = $_GET['msg'];
$type = $_GET['type'];
$goto = $_GET['goto'] ?? 'index.php';

if($type == 'success'){
    $color = 'success';
} else {
    $color = 'error';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Message</title>
    <link rel="stylesheet" href="//static.paradowx2.sbs/style.css">
</head>
<body>
<div class="card result-card">
    <h1>Message</h1>
    <div class="msg msg-<?php echo $color; ?>">
        <?php echo htmlspecialchars($msg); ?>
    </div>
    <p class="countdown">Redirecting to <?php echo htmlspecialchars($goto); ?> in <span id="sec">3</span> seconds...</p>
</div>
<script>
    var sec = 3;
    setInterval(function () {
        sec--;
        var el = document.getElementById('sec');
        if (el) el.textContent = sec;
    }, 1000);

    setTimeout(function () {
        var params = new URLSearchParams(window.location.search);
        location.href = params.get('goto') || 'index.php';
    }, 3000);
</script></body>
</html>