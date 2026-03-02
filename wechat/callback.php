<?php

declare(strict_types=1);

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../lib/db.php';
require_once __DIR__ . '/../lib/helpers.php';
require_once __DIR__ . '/../user/sign_process.php';

$state = $_GET['state'] ?? '';
$code = $_GET['code'] ?? '';
$mockOpenid = $_GET['mock_openid'] ?? '';
$parsed = parseState($state);

$openid = '';
if (DEV_MODE && $mockOpenid !== '') {
    $openid = $mockOpenid;
} elseif ($code !== '') {
    $tokenUrl = 'https://api.weixin.qq.com/sns/oauth2/access_token?appid=' . WECHAT_APPID
        . '&secret=' . WECHAT_SECRET
        . '&code=' . urlencode($code)
        . '&grant_type=authorization_code';
    $raw = @file_get_contents($tokenUrl);
    $data = $raw ? json_decode($raw, true) : [];
    $openid = $data['openid'] ?? '';
}

if ($openid === '') {
    exit('授权失败：未获取到openid');
}

if ($parsed['type'] === 'register') {
    $url = '/user/register_form.php?activity_id=' . urlencode($parsed['activity_id']) . '&openid=' . urlencode($openid);
    header('Location: ' . $url);
    exit;
}

if ($parsed['type'] === 'sign') {
    $result = processSign($openid, $parsed['activity_id'], (int)($parsed['sign_index'] ?? 1));
    $url = '/user/sign_result.php?' . http_build_query($result);
    header('Location: ' . $url);
    exit;
}

exit('state 无效');
