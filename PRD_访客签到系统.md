# 访客签到系统 PRD 文档

## 1. 项目概述

### 1.1 项目背景
开发一个访客签到系统，满足活动、会议或园区访客的线上登记与签到需求。系统分为管理端（电脑端）和用户端（手机端），管理端负责生成访客登记链接和签到二维码，用户端通过微信授权完成信息登记和签到操作。

### 1.2 核心目标
- 实现访客信息的线上化登记管理。
- 利用微信授权机制确保用户身份唯一性。
- 提供灵活的签到规则配置（时间、次数）。
- 支持多场次/多活动独立管理（通过 ID 区分）。

### 1.3 适用场景
- 会议/活动签到。
- 园区/办公楼访客登记。
- 多日培训课程签到。

## 2. 功能模块与业务流程

### 2.1 整体流程
1. 管理端创建登记活动，生成登记链接与二维码。
2. 用户端扫码进入登记页面，完成微信授权并提交访客信息。
3. 管理端配置签到规则（签到次数与每次时间窗口），生成签到二维码。
4. 用户端扫码签到，系统进行静默授权并自动校验资格。
5. 系统记录签到结果，管理端可查看签到记录与失败原因。

### 2.2 管理端功能（电脑端）
| 模块 | 功能点 | 说明 |
|---|---|---|
| 访客登记管理 | 创建登记活动 | 设置登记开始时间、结束时间，保存后生成临时链接、二维码和唯一 ID |
| 访客登记管理 | 查看登记列表 | 按 ID 查看已登记的访客信息 |
| 签到规则配置 | 创建签到任务 | 选择对应 ID，设置签到开始/结束时间，选择签到方式（仅二维码） |
| 签到规则配置 | 设置签到次数 | 若超过 2 次，需分别设置每一次的起止时间 |
| 签到规则配置 | 生成签到码 | 配置完成后自动生成签到二维码 |
| 数据查询 | 查看签到记录 | 查看某 ID 下的所有签到记录，包括签到时间、用户信息 |

### 2.3 用户端功能（手机端）
| 模块 | 功能点 | 说明 |
|---|---|---|
| 访客登记 | 扫码进入 | 扫描管理员生成的登记二维码 |
| 访客登记 | 微信授权 | 点击按钮触发微信授权，获取 OpenID |
| 访客登记 | 填写表单 | 姓名、单位名称、手机号（必填）；是否有车辆（若选“是”，弹出车牌号输入框） |
| 访客登记 | 提交登记 | 所有字段校验通过后保存，与 OpenID 绑定 |
| 活动签到 | 扫码签到 | 扫描管理员生成的签到二维码 |
| 活动签到 | 自动授权 | 进入页面后静默授权获取 code（使用 snsapi_base） |
| 活动签到 | 结果反馈 | 显示“签到成功”（含时间）或“签到失败”及原因 |

## 3. 数据库设计（MySQL 5.7）

### 3.1 数据表结构

#### 表 1：登记活动表（`registration_activities`）
| 字段名 | 类型 | 说明 |
|---|---|---|
| id | INT AUTO_INCREMENT | 主键，自增 |
| activity_id | VARCHAR(50) UNIQUE | 唯一 ID（对外展示用，如 RG2025001） |
| name | VARCHAR(100) | 活动名称（如“开发者大会”） |
| start_time | DATETIME | 登记开始时间 |
| end_time | DATETIME | 登记结束时间 |
| link_token | VARCHAR(64) UNIQUE | 链接唯一标识（用于生成 URL） |
| qrcode_path | VARCHAR(255) | 二维码图片存储路径 |
| created_at | TIMESTAMP | 创建时间 |

#### 表 2：访客登记表（`visitors`）
| 字段名 | 类型 | 说明 |
|---|---|---|
| id | INT AUTO_INCREMENT | 主键 |
| activity_id | VARCHAR(50) | 关联登记活动 ID |
| openid | VARCHAR(64) | 微信用户唯一标识（索引） |
| name | VARCHAR(50) | 姓名 |
| company | VARCHAR(100) | 单位名称 |
| mobile | VARCHAR(20) | 手机号 |
| has_car | TINYINT(1) | 是否有车（0 无 / 1 有） |
| plate_number | VARCHAR(20) | 车牌号（`has_car=1` 时必填） |
| registered_at | TIMESTAMP | 登记时间 |

#### 表 3：签到规则表（`sign_rules`）
| 字段名 | 类型 | 说明 |
|---|---|---|
| id | INT AUTO_INCREMENT | 主键 |
| activity_id | VARCHAR(50) | 关联登记活动 ID |
| sign_count | TINYINT | 总签到次数（如 1 次、2 次） |
| start_time_1 | DATETIME | 第 1 次签到开始时间 |
| end_time_1 | DATETIME | 第 1 次签到结束时间 |
| start_time_2 | DATETIME | 第 2 次签到开始时间（若 `sign_count ≥ 2`） |
| end_time_2 | DATETIME | 第 2 次签到结束时间 |
| sign_token | VARCHAR(64) UNIQUE | 签到链接唯一标识 |
| qrcode_path | VARCHAR(255) | 签到二维码路径 |
| created_at | TIMESTAMP | 创建时间 |

#### 表 4：签到记录表（`sign_logs`）
| 字段名 | 类型 | 说明 |
|---|---|---|
| id | INT AUTO_INCREMENT | 主键 |
| activity_id | VARCHAR(50) | 关联登记活动 ID |
| openid | VARCHAR(64) | 用户 OpenID |
| sign_time | DATETIME | 签到时间 |
| sign_index | TINYINT | 第几次签到（如 1、2） |
| status | VARCHAR(20) | 成功/失败（记录失败便于排查） |

## 4. 微信网页授权详细实现

### 4.1 授权流程说明
根据微信官方文档，本项目采用两种授权方式：
- 登记时：使用 `snsapi_userinfo`，需用户手动点击按钮触发授权（符合规范）。
- 签到时：使用 `snsapi_base`，静默授权，仅获取 OpenID 用于比对。

### 4.2 核心接口调用（PHP 后端）

#### 4.2.1 配置参数
```php
// 微信配置（存储在 config.php）
define('APPID', 'your_appid');
define('SECRET', 'your_appsecret');
define('REDIRECT_URI', urlencode('https://yourdomain.com/wechat/callback.php'));
```

#### 4.2.2 构造授权链接
```php
// 登记页面触发授权
$scope = 'snsapi_userinfo'; // 需用户确认
$state = 'register_' . $activity_id; // 携带活动ID
$url = "https://open.weixin.qq.com/connect/oauth2/authorize?appid=" . APPID . "&redirect_uri=" . REDIRECT_URI . "&response_type=code&scope=" . $scope . "&state=" . $state . "#wechat_redirect";

// 签到页面自动跳转（静默）
$scope = 'snsapi_base';
$state = 'sign_' . $activity_id . '_' . $sign_index;
// 页面加载时通过 JS 跳转或后端 302 重定向
```

#### 4.2.3 回调处理（`callback.php`）
```php
// 获取 code 和 state
$code = $_GET['code'] ?? '';
$state = $_GET['state'] ?? '';

// 用 code 换取 access_token 和 openid
$token_url = "https://api.weixin.qq.com/sns/oauth2/access_token?appid=" . APPID . "&secret=" . SECRET . "&code=" . $code . "&grant_type=authorization_code";
$response = file_get_contents($token_url);
$data = json_decode($response, true);

if (isset($data['openid'])) {
    $openid = $data['openid'];

    // 根据 state 判断业务类型
    if (strpos($state, 'register_') === 0) {
        // 登记流程：跳转到登记表单页，携带 openid
        $activity_id = str_replace('register_', '', $state);
        header("Location: register_form.php?openid=" . $openid . "&activity_id=" . $activity_id);
    } elseif (strpos($state, 'sign_') === 0) {
        // 签到流程：直接处理签到逻辑
        $parts = explode('_', $state);
        $activity_id = $parts[1];
        $sign_index = $parts[2];

        // 验证签到资格并记录
        $result = processSign($openid, $activity_id, $sign_index);
        // 显示结果页面
        header("Location: sign_result.php?" . http_build_query($result));
    }
} else {
    // 错误处理
    echo "授权失败：" . $data['errmsg'];
}
```

#### 4.2.4 获取用户信息（登记时可选）
```php
// 仅在 snsapi_userinfo 模式下调用
if ($data['scope'] == 'snsapi_userinfo' && isset($data['access_token'])) {
    $userinfo_url = "https://api.weixin.qq.com/sns/userinfo?access_token=" . $data['access_token'] . "&openid=" . $openid . "&lang=zh_CN";
    $userinfo = json_decode(file_get_contents($userinfo_url), true);
    // 可获取昵称、头像等，但本项目仅需 openid
}
```

### 4.3 安全性注意事项
- `secret` 绝不暴露：服务端保存，所有微信接口请求由后端发起。
- `code` 一次性使用：每次授权 code 仅 5 分钟有效，用完即销毁。
- `state` 参数防篡改：携带业务标识，防止回调被恶意利用。
- `OpenID` 唯一约束：数据库设置 unique 索引，确保同一活动一个用户只登记一次。
- 签到防重：检查 `sign_logs` 表中是否已有该用户某次签到记录。

## 5. 页面与交互设计

### 5.1 管理端页面（电脑端）
| 页面 | 元素 | 说明 |
|---|---|---|
| 登记配置页 | 活动名称输入框、时间选择器（开始/结束）、保存按钮 | 保存后跳转到详情页 |
| 登记详情页 | 显示临时链接、二维码（使用 PHP QR Code 库生成）、ID | 提供复制链接、下载二维码按钮 |
| 签到配置页 | 选择 ID 下拉框、签到次数选择（1-5）、时间设置面板（动态增减）、生成按钮 | 超过 2 次时动态增加时间输入框 |
| 数据查看页 | 列表展示登记/签到记录，可按 ID 筛选，支持导出 Excel | 显示签到时间、用户信息 |

### 5.2 用户端页面（手机端）
| 页面 | 元素 | 交互说明 |
|---|---|---|
| 登记引导页 | 说明文字、“微信授权登记”按钮 | 点击按钮跳转微信授权链接 |
| 登记表单页 | 姓名、单位、手机号输入框；是否有车（单选框）；车辆输入框（动态显示）；确认提交按钮 | 所有字段实时校验；提交后存入数据库 |
| 签到中页面 | 加载动画（正在验证...） | 自动跳转授权或显示结果 |
| 签到结果页 | 成功：显示“签到成功”及时间；失败：显示失败原因（如未登记/时间不符/已签到） | 失败原因具体化（如“未找到您的登记信息”） |

## 6. 技术实现要点

### 6.1 后端技术栈
- PHP 7.4+：处理业务逻辑、微信接口调用。
- MySQL 5.7：数据存储。
- GD 库 / QR Code 库：生成二维码。
- Session / Cookie：管理端登录态保持。

### 6.2 前端技术栈
- HTML5：页面结构。
- CSS3（Flex/Grid）：响应式布局，适配手机与电脑。
- 原生 JavaScript（ES6）：表单验证、动态显示车牌输入框、复制链接功能。
- Viewport 设置：确保手机端缩放正常。
