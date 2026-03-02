<?php

declare(strict_types=1);

require_once __DIR__ . '/../lib/db.php';
require_once __DIR__ . '/../lib/helpers.php';

$token = trim($_GET['token'] ?? '');
if ($token === '') {
    exit('缺少 token');
}

$stmt = db()->prepare('SELECT * FROM registration_activities WHERE link_token = :token');
$stmt->execute([':token' => $token]);
$activity = $stmt->fetch();
if (!$activity) {
    exit('登记链接无效');
}

$now = strtotime(date('Y-m-d H:i:s'));
if ($now < strtotime($activity['start_time']) || $now > strtotime($activity['end_time'])) {
    exit('不在登记时间范围内');
}

$authUrl = '/wechat/oauth_start.php?type=register&activity_id=' . urlencode($activity['activity_id']);
?>
<!doctype html><html lang="zh-CN"><head><meta charset="UTF-8"><title>访客登记</title></head>
<body>
<h2><?= h($activity['name']) ?> - 访客登记</h2>
<p>请点击按钮完成微信授权后填写登记信息。</p>
<a href="<?= h($authUrl) ?>">微信授权登记</a>
</body></html>
