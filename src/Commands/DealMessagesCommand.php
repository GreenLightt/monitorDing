<?php namespace Redgo\MonitorDing\Commands;

use Illuminate\Console\Command;
use Redgo\MonitorDing\Facades\MonitorDing;
use Redgo\MonitorDing\MonitorDingClient;
use Cache;

class DealMessagesCommand extends Command {

    protected $signature = 'monitorDing:dealMessages';

    protected $description = '处理已积攒的消息';

    /**
     * The Monitor Client
     *
     * @var MonitorDingClient
     */
    protected $monitor;

    public function __construct(MonitorDingClient $monitor)
    {
        parent::__construct();
        $this->monitor = $monitor;
    }

    public function handle() {
        $error_arr = json_decode(Cache::store('file')->get('monitorDingError', ''));

        if (count($error_arr) != 0) {
            $text = view('monitorDing::exceptions.report', ['items' => $error_arr])->render();
            $this->monitor->sendMarkdown('错误日志汇总', $text);
            Cache::store('file')->forget('monitorDingError');
        }
    }
}