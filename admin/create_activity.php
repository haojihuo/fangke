<?php

declare(strict_types=1);

require_once __DIR__ . '/../lib/db.php';
require_once __DIR__ . '/../lib/helpers.php';
require_once __DIR__ . '/../config.php';

$msg = '';
$activity = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $start = trim($_POST['start_time'] ?? '');
    $end = trim($_POST['end_time'] ?? '');

    if ($name === '' || $start === '' || $end === '') {
        $msg = '请填写完整信息';
    } elseif (strtotime($start) >= strtotime($end)) {
        $msg = '登记开始时间必须早于结束时间';
    } else {
        $activityId = genActivityId();
        $token = genToken(32);
        $registerUrl = APP_URL . '/user/register.php?token=' . urlencode($token);
        $qrUrl = qrImageUrl($registerUrl);

        $sql = 'INSERT INTO registration_activities(activity_id, name, start_time, end_time, link_token, qrcode_path)
                VALUES(:activity_id, :name, :start_time, :end_time, :token, :qrcode)';
        db()->prepare($sql)->execute([
            ':activity_id' => $activityId,
            ':name' => $name,
            ':start_time' => $start,
            ':end_time' => $end,
            ':token' => $token,
            ':qrcode' => $qrUrl,
        ]);

        $activity = [
            'activity_id' => $activityId,
            'register_url' => $registerUrl,
            'qrcode' => $qrUrl,
        ];
        $msg = '创建成功';
    }
}
?>
<!doctype html>
<html lang="zh-CN"><head><meta charset="UTF-8"><title>创建登记活动</title></head>
<body>
<h2>创建登记活动</h2>
<p style="color:#d00;"><?= h($msg) ?></p>
<form method="post">
    <label>活动名称 <input name="name" required></label><br><br>
    <label>开始时间 <input type="datetime-local" name="start_time" required></label><br><br>
    <label>结束时间 <input type="datetime-local" name="end_time" required></label><br><br>
    <button type="submit">保存并生成链接</button>
</form>
<?php if ($activity): ?>
<hr>
<p>活动ID：<?= h($activity['activity_id']) ?></p>
<p>登记链接：<a href="<?= h($activity['register_url']) ?>" target="_blank"><?= h($activity['register_url']) ?></a></p>
<p><img src="<?= h($activity['qrcode']) ?>" alt="登记二维码"></p>
<?php endif; ?>
<p><a href="/index.php">返回首页</a></p>
</body></html>
