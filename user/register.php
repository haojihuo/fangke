<?php

declare(strict_types=1);

require_once __DIR__ . '/../lib/db.php';
require_once __DIR__ . '/../lib/helpers.php';
require_once __DIR__ . '/../config.php';

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

$openid = trim($_GET['openid'] ?? '');
$authUrl = '/wechat/oauth_start.php?type=register&activity_id=' . urlencode($activity['activity_id']);

if (DEV_MODE && $openid === '') {
    // 本地调试模式：无需真实微信环境，自动提供一个模拟 openid 直达表单
    $authUrl .= '&mock_openid=openid_dev_' . urlencode($activity['activity_id']);
}
?>
<!doctype html><html lang="zh-CN"><head><meta charset="UTF-8"><title>访客登记</title></head>
<body>
<h2><?= h($activity['name']) ?> - 访客登记</h2>
<?php if ($openid === ''): ?>
    <p>请点击按钮完成微信授权后填写登记信息。</p>
    <a href="<?= h($authUrl) ?>">微信授权登记</a>
<?php else: ?>
    <p style="color:green;">已完成微信授权，请填写登记信息。</p>
    <form method="post" action="/user/save_registration.php">
        <input type="hidden" name="activity_id" value="<?= h($activity['activity_id']) ?>">
        <input type="hidden" name="openid" value="<?= h($openid) ?>">
        <label>姓名 <input name="name" required></label><br><br>
        <label>单位 <input name="company" required></label><br><br>
        <label>手机号 <input name="mobile" required></label><br><br>
        <label>是否有车
            <select name="has_car" id="has_car"><option value="0">否</option><option value="1">是</option></select>
        </label><br><br>
        <div id="plate_box" style="display:none;">
            <label>车牌号 <input name="plate_number" id="plate_number"></label><br><br>
        </div>
        <button type="submit">提交登记</button>
    </form>
    <script>
    const hasCar = document.getElementById('has_car');
    const box = document.getElementById('plate_box');
    const plate = document.getElementById('plate_number');
    hasCar.addEventListener('change', () => {
        const show = hasCar.value === '1';
        box.style.display = show ? 'block' : 'none';
        plate.required = show;
    });
    </script>
<?php endif; ?>
</body></html>
