# 访客签到系统（PHP + MySQL）

根据 `PRD_访客签到系统.md` 实现的基础可运行版本，包含：

- 管理端：创建登记活动、配置签到规则、查看登记/签到记录
- 用户端：微信授权登记、扫码签到、签到结果反馈
- 微信回调：支持 `snsapi_userinfo` / `snsapi_base` 流程（并支持本地 `mock_openid` 调试）

## 快速开始

1. 创建数据库并导入表结构：
   - 执行 `sql/schema.sql`
2. 修改 `config.php` 中数据库和微信参数
3. 启动 PHP 内置服务：

```bash
php -S 0.0.0.0:8000
```

4. 浏览器访问：
   - `http://localhost:8000/index.php`

## 调试微信授权

开发环境可在授权入口追加 `mock_openid`：

```text
/wechat/oauth_start.php?type=register&activity_id=RG2025001&mock_openid=openid_test_1
```

即可跳过真实微信授权流程。
