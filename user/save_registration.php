<?php

declare(strict_types=1);

require_once __DIR__ . '/../lib/db.php';
require_once __DIR__ . '/../lib/helpers.php';

$activityId = trim($_POST['activity_id'] ?? '');
$openid = trim($_POST['openid'] ?? '');
$name = trim($_POST['name'] ?? '');
$company = trim($_POST['company'] ?? '');
$mobile = trim($_POST['mobile'] ?? '');
$hasCar = (int)($_POST['has_car'] ?? 0);
$plate = trim($_POST['plate_number'] ?? '');

if ($activityId === '' || $openid === '' || $name === '' || $company === '' || $mobile === '') {
    exit('请完整填写必填信息');
}
if ($hasCar === 1 && $plate === '') {
    exit('有车时车牌号必填');
}

$sql = 'INSERT INTO visitors(activity_id, openid, name, company, mobile, has_car, plate_number)
        VALUES(:activity_id,:openid,:name,:company,:mobile,:has_car,:plate)
        ON DUPLICATE KEY UPDATE name=VALUES(name),company=VALUES(company),mobile=VALUES(mobile),has_car=VALUES(has_car),plate_number=VALUES(plate_number)';
db()->prepare($sql)->execute([
    ':activity_id' => $activityId,
    ':openid' => $openid,
    ':name' => $name,
    ':company' => $company,
    ':mobile' => $mobile,
    ':has_car' => $hasCar,
    ':plate' => $hasCar === 1 ? $plate : null,
]);

echo '登记成功';
