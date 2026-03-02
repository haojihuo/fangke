<?php

declare(strict_types=1);

require_once __DIR__ . '/../config.php';

$type = $_GET['type'] ?? '';
$activityId = $_GET['activity_id'] ?? '';
$signIndex = isset($_GET['sign_index']) ? (int)$_GET['sign_index'] : null;

if (!in_array($type, ['register', 'sign'], true) || $activityId === '') {
    exit('参数错误');
}

$scope = $type === 'register' ? 'snsapi_userinfo' : 'snsapi_base';
$state = $type . '|' . $activityId . '|' . (string)($signIndex ?? 0);
$redirectUri = urlencode(WECHAT_REDIRECT_URI);

$authUrl = 'https://open.weixin.qq.com/connect/oauth2/authorize?appid=' . WECHAT_APPID
    . '&redirect_uri=' . $redirectUri
    . '&response_type=code&scope=' . $scope
    . '&state=' . urlencode($state)
    . '#wechat_redirect';

if (DEV_MODE && isset($_GET['mock_openid'])) {
    $devUrl = WECHAT_REDIRECT_URI . '?state=' . urlencode($state) . '&mock_openid=' . urlencode((string)$_GET['mock_openid']);
    header('Location: ' . $devUrl);
    exit;
}

header('Location: ' . $authUrl);
