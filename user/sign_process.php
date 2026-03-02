<?php

declare(strict_types=1);

require_once __DIR__ . '/../lib/db.php';

function processSign(string $openid, string $activityId, int $signIndex): array
{
    $pdo = db();
    $signTime = date('Y-m-d H:i:s');

    $visitorStmt = $pdo->prepare('SELECT id FROM visitors WHERE activity_id=:aid AND openid=:openid');
    $visitorStmt->execute([':aid' => $activityId, ':openid' => $openid]);
    if (!$visitorStmt->fetch()) {
        logSign($activityId, $openid, $signIndex, '失败', '未找到登记信息', $signTime);
        return ['status' => '失败', 'message' => '未找到您的登记信息', 'time' => $signTime];
    }

    $ruleStmt = $pdo->prepare('SELECT * FROM sign_rules WHERE activity_id=:aid');
    $ruleStmt->execute([':aid' => $activityId]);
    $rule = $ruleStmt->fetch();
    if (!$rule) {
        logSign($activityId, $openid, $signIndex, '失败', '未配置签到规则', $signTime);
        return ['status' => '失败', 'message' => '活动未配置签到规则', 'time' => $signTime];
    }

    $windowStart = $signIndex === 1 ? $rule['start_time_1'] : $rule['start_time_2'];
    $windowEnd = $signIndex === 1 ? $rule['end_time_1'] : $rule['end_time_2'];
    $now = strtotime($signTime);
    if ($windowStart === null || $windowEnd === null || $now < strtotime($windowStart) || $now > strtotime($windowEnd)) {
        logSign($activityId, $openid, $signIndex, '失败', '不在签到时间范围', $signTime);
        return ['status' => '失败', 'message' => '当前不在签到时间范围', 'time' => $signTime];
    }

    $dupStmt = $pdo->prepare('SELECT id FROM sign_logs WHERE activity_id=:aid AND openid=:openid AND sign_index=:idx AND status="成功"');
    $dupStmt->execute([':aid' => $activityId, ':openid' => $openid, ':idx' => $signIndex]);
    if ($dupStmt->fetch()) {
        logSign($activityId, $openid, $signIndex, '失败', '重复签到', $signTime);
        return ['status' => '失败', 'message' => '您已完成本次签到', 'time' => $signTime];
    }

    logSign($activityId, $openid, $signIndex, '成功', '签到成功', $signTime);
    return ['status' => '成功', 'message' => '签到成功', 'time' => $signTime];
}

function logSign(string $activityId, string $openid, int $signIndex, string $status, string $message, string $time): void
{
    db()->prepare('INSERT INTO sign_logs(activity_id,openid,sign_time,sign_index,status,message) VALUES(:aid,:openid,:time,:idx,:status,:message)')
        ->execute([
            ':aid' => $activityId,
            ':openid' => $openid,
            ':time' => $time,
            ':idx' => $signIndex,
            ':status' => $status,
            ':message' => $message,
        ]);
}
