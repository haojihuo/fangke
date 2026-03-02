<?php

declare(strict_types=1);

require_once __DIR__ . '/../lib/helpers.php';

$activityId = trim($_GET['activity_id'] ?? '');
$openid = trim($_GET['openid'] ?? '');
if ($activityId === '' || $openid === '') {
    exit('参数无效');
}
?>
<!doctype html><html lang="zh-CN"><head><meta charset="UTF-8"><title>填写登记信息</title></head>
<body>
<h2>填写登记信息</h2>
<form method="post" action="/user/save_registration.php">
    <input type="hidden" name="activity_id" value="<?= h($activityId) ?>">
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
</body></html>
