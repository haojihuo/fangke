<?php

declare(strict_types=1);

// ===== 基础配置 =====
const APP_URL = 'http://localhost:8000';

// ===== MySQL 配置 =====
const DB_HOST = '127.0.0.1';
const DB_PORT = 3306;
const DB_NAME = 'visitor_checkin';
const DB_USER = 'root';
const DB_PASS = 'root';
const DB_CHARSET = 'utf8mb4';

// ===== 微信配置 =====
const WECHAT_APPID = 'your_appid';
const WECHAT_SECRET = 'your_appsecret';
const WECHAT_REDIRECT_URI = APP_URL . '/wechat/callback.php';

// 开发模式：true 时支持通过 ?mock_openid=xxx 直接走回调
const DEV_MODE = true;
