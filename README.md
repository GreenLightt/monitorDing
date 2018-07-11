# monitorDing
Laravel 插件，用于给钉钉自定义机器人发送消息

本插件是采用中间件捕捉异常，与 `debugbar` 采用同一种方式进行异常的搜集，所以不能并存；如果要使用本插件，确保 `debugbar` 是关闭的；

# 引入步骤

`composer` 引入

```
composer require redgo/monitor-ding:^0.2
```

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

1. 在配置文件 `config/monitorDing` 修改 `webhook` 值
