<?php
namespace Redgo\MonitorDing\Middleware;

use Error;
use Cache;
use Closure;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Contracts\Container\Container;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Support\Str;
use Redgo\MonitorDing\MonitorDingClient;
use Symfony\Component\Debug\Exception\FatalThrowableError;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class Monitor
{
    /**
     * The App container
     *
     * @var Container
     */
    protected $container;

    /**
     * The Monitor Client
     *
     * @var MonitorDingClient
     */
    protected $monitor;

    /**
     * Create a new middleware instance.
     *
     * @param  Container $container
     */
    public function __construct(Container $container, MonitorDingClient $monitor)
    {
        $this->container = $container;
        $this->monitor = $monitor;
    }

    /**
     * Handle an incoming request.
     *
     * @param  Request  $request
     * @param  Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $project_name = config('monitorDing.project_name', '');
        if (! empty($project_name)) {
            $project_name = sprintf("【%s】", $project_name);
        }

        try {
            $response = $next($request);
        } catch (NotFoundHttpException $e) {
            $response = $this->handleException($request, $e);
            if (! $this->filter($request->fullUrl())) {
                $this->customHandle(sprintf("%s url：%s  404 not found", $project_name, $request->fullUrl()));
            }
        } catch (Exception $e) {
            $response = $this->handleException($request, $e);
            if (! $this->filter($e->getMessage())) {
                $this->customHandle(sprintf("%s 文件：%s (%s 行) 内容：%s", $project_name, $e->getFile(), $e->getLine(), $e->getMessage()));
            }
        } catch (Error $error) {
            $e = new FatalThrowableError($error);
            $response = $this->handleException($request, $e);
            if (! $this->filter($e->getMessage())) {
                $this->customHandle(sprintf("%s 文件：%s (%s 行) 内容：%s", $project_name, $e->getFile(), $e->getLine(), $e->getMessage()));
            }
        }

        return $response;
    }

    /**
     * 自定义处理
     *
     * @param $info
     */
    protected function customHandle($info) {
        $enabled = config('monitorDing.enabled');
        // 是否有开启 cron ， 如果开启，则可以汇总发送
        $cron = config('monitorDing.cron', false);

        if ($enabled) {
            if ($cron) {
                $old = Cache::store('file')->get('monitorDingError', '');
                $error_arr = [];

                if (! empty($old)) {
                    $error_arr = json_decode($old);
                }

                $error_arr[] = $info;
                // 保存 5 分钟
                Cache::store('file')->put('monitorDingError', json_encode($error_arr), 5);
            } else {
                $this->monitor->sendText($info);
            }
        }
    }

    /**
     * 判断是否包含需要过滤的内容
     */
    public function filter($content) {
        $filter_pieces = config('monitorDing.filter', []);

        foreach ($filter_pieces as $filter_piece) {
            if (Str::contains($filter_piece, $content)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Handle the given exception.
     *
     * (Copy from Illuminate\Routing\Pipeline by Taylor Otwell)
     *
     * @param $passable
     * @param  Exception $e
     * @return mixed
     * @throws Exception
     */
    protected function handleException($passable, Exception $e)
    {
        if (! $this->container->bound(ExceptionHandler::class) || ! $passable instanceof Request) {
            throw $e;
        }

        $handler = $this->container->make(ExceptionHandler::class);

        $handler->report($e);

        return $handler->render($passable, $e);
    }
}
