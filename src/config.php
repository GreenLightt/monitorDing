<?php

return [
    // 是否开启报错写入
    'enabled' => true,

    // 因为钉钉限制一分钟最多发 20 条，所以可以先汇总再通过 cron 统一每隔一分钟发送一次；
    // 如果服务器没开启 cron 可以选择 false
    'cron' => true,

    // curl证书验证, 线下环境不用开启
    'curl_verify' => false,

    // webhook的值
    'webhook' => '',

    // 项目名称
    'project_name' => '',

    // 过滤关键词
    'filter' => [],
];