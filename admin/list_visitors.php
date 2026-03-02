<?php

declare(strict_types=1);

require_once __DIR__ . '/../lib/db.php';
require_once __DIR__ . '/../lib/helpers.php';

$activityId = trim($_GET['activity_id'] ?? '');
$sql = 'SELECT * FROM visitors';
$params = [];
if ($activityId !== '') {
    $sql .= ' WHERE activity_id = :activity_id';
    $params[':activity_id'] = $activityId;
}
$sql .= ' ORDER BY id DESC';
$stmt = db()->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll();
?>
<!doctype html><html lang="zh-CN"><head><meta charset="UTF-8"><title>登记列表</title></head>
<body>
<h2>访客登记列表</h2>
<form>
    <label>活动ID筛选 <input name="activity_id" value="<?= h($activityId) ?>"></label>
    <button type="submit">查询</button>
</form>
<table border="1" cellpadding="6"><tr><th>活动ID</th><th>openid</th><th>姓名</th><th>单位</th><th>手机号</th><th>车牌</th><th>时间</th></tr>
<?php foreach ($rows as $r): ?>
<tr>
<td><?= h($r['activity_id']) ?></td><td><?= h($r['openid']) ?></td><td><?= h($r['name']) ?></td>
<td><?= h($r['company']) ?></td><td><?= h($r['mobile']) ?></td><td><?= h((string)$r['plate_number']) ?></td>
<td><?= h($r['registered_at']) ?></td>
</tr>
<?php endforeach; ?>
</table>
<p><a href="/index.php">返回首页</a></p>
</body></html>
