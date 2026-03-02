<?php

declare(strict_types=1);

require_once __DIR__ . '/../lib/db.php';
require_once __DIR__ . '/../lib/helpers.php';
require_once __DIR__ . '/../config.php';

$activities = db()->query('SELECT activity_id, name FROM registration_activities ORDER BY id DESC')->fetchAll();
$msg = '';
$rule = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $activityId = trim($_POST['activity_id'] ?? '');
    $count = (int)($_POST['sign_count'] ?? 1);
    $s1 = trim($_POST['start_time_1'] ?? '');
    $e1 = trim($_POST['end_time_1'] ?? '');
    $s2 = trim($_POST['start_time_2'] ?? '');
    $e2 = trim($_POST['end_time_2'] ?? '');

    if ($activityId === '' || $s1 === '' || $e1 === '') {
        $msg = '请填写必填项';
    } elseif ($count === 2 && ($s2 === '' || $e2 === '')) {
        $msg = '2次签到需要填写第2次时间';
    } else {
        $token = genToken(32);
        $signUrl = APP_URL . '/user/sign.php?token=' . urlencode($token);
        $qrUrl = qrImageUrl($signUrl);

        $sql = 'INSERT INTO sign_rules(activity_id, sign_count, start_time_1, end_time_1, start_time_2, end_time_2, sign_token, qrcode_path)
                VALUES(:activity_id,:count,:s1,:e1,:s2,:e2,:token,:qrcode)
                ON DUPLICATE KEY UPDATE sign_count=VALUES(sign_count),start_time_1=VALUES(start_time_1),end_time_1=VALUES(end_time_1),
                start_time_2=VALUES(start_time_2),end_time_2=VALUES(end_time_2),sign_token=VALUES(sign_token),qrcode_path=VALUES(qrcode_path)';
        db()->prepare($sql)->execute([
            ':activity_id' => $activityId,
            ':count' => $count,
            ':s1' => $s1,
            ':e1' => $e1,
            ':s2' => $count === 2 ? $s2 : null,
            ':e2' => $count === 2 ? $e2 : null,
            ':token' => $token,
            ':qrcode' => $qrUrl,
        ]);

        $rule = ['sign_url' => $signUrl, 'qrcode' => $qrUrl];
        $msg = '签到规则保存成功';
    }
}
?>
<!doctype html>
<html lang="zh-CN"><head><meta charset="UTF-8"><title>创建签到规则</title></head>
<body>
<h2>创建签到规则</h2>
<p style="color:#d00;"><?= h($msg) ?></p>
<form method="post">
    <label>活动
        <select name="activity_id" required>
            <option value="">请选择</option>
            <?php foreach ($activities as $act): ?>
                <option value="<?= h($act['activity_id']) ?>"><?= h($act['activity_id'] . ' - ' . $act['name']) ?></option>
            <?php endforeach; ?>
        </select>
    </label><br><br>
    <label>签到次数
        <select name="sign_count" id="count"><option value="1">1次</option><option value="2">2次</option></select>
    </label><br><br>
    <label>第1次开始 <input type="datetime-local" name="start_time_1" required></label>
    <label>第1次结束 <input type="datetime-local" name="end_time_1" required></label><br><br>
    <div id="times2" style="display:none;">
        <label>第2次开始 <input type="datetime-local" name="start_time_2"></label>
        <label>第2次结束 <input type="datetime-local" name="end_time_2"></label><br><br>
    </div>
    <button type="submit">保存并生成签到二维码</button>
</form>
<?php if ($rule): ?>
<p>签到链接：<a href="<?= h($rule['sign_url']) ?>" target="_blank"><?= h($rule['sign_url']) ?></a></p>
<p><img src="<?= h($rule['qrcode']) ?>" alt="签到二维码"></p>
<?php endif; ?>
<script>
const count = document.getElementById('count');
const panel = document.getElementById('times2');
count.addEventListener('change', () => panel.style.display = count.value === '2' ? 'block' : 'none');
</script>
<p><a href="/index.php">返回首页</a></p>
</body></html>
