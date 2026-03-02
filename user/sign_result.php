<?php

declare(strict_types=1);

require_once __DIR__ . '/../lib/helpers.php';

$status = $_GET['status'] ?? '失败';
$message = $_GET['message'] ?? '未知错误';
$time = $_GET['time'] ?? '';
?>
<!doctype html><html lang="zh-CN"><head><meta charset="UTF-8"><title>签到结果</title></head>
<body>
<h2>签到<?= $status === '成功' ? '成功' : '失败' ?></h2>
<p><?= h($message) ?></p>
<?php if ($time !== ''): ?><p>时间：<?= h($time) ?></p><?php endif; ?>
</body></html>
