<?php
namespace Redgo\MonitorDing;

class MonitorDingClient {

    protected $webhook;

    public function __construct($webhook)
    {
        $this->webhook = $webhook;
    }
}