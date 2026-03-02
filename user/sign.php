<?php

declare(strict_types=1);

require_once __DIR__ . '/../lib/db.php';

$token = trim($_GET['token'] ?? '');
if ($token === '') {
    exit('缺少 token');
}
$stmt = db()->prepare('SELECT * FROM sign_rules WHERE sign_token = :token');
$stmt->execute([':token' => $token]);
$rule = $stmt->fetch();
if (!$rule) {
    exit('签到链接无效');
}

$signIndex = isset($_GET['sign_index']) ? (int)$_GET['sign_index'] : 1;
if ($signIndex < 1 || $signIndex > (int)$rule['sign_count']) {
    exit('签到次序无效');
}

$url = '/wechat/oauth_start.php?type=sign&activity_id=' . urlencode($rule['activity_id']) . '&sign_index=' . $signIndex;
header('Location: ' . $url);
