<?php namespace Redgo\MonitorDing\Facades;

use Illuminate\Support\Facades\Facade;

class MonitorDing extends Facade {

    /**
     * Return facade accessor
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'monitorDing';
    }
}