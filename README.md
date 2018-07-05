# monitorDing
Laravel 插件，用于给钉钉自定义机器人发送消息

# 引入步骤

发布视图

```
php artisan vendor:publish
```

在 `config/app.php` 添加

```
Redgo\MonitorDing\MonitorDingServiceProvider::class,
```


如果需要配置门面模式， 在 `config/app.php` 添加

```
'MonitorDing' => Redgo\MonitorDing\Facades\MonitorDing::class,
```


# 使用

1. 首先在配置文件 `config/monitorDing` 修改 `webhook` 值
