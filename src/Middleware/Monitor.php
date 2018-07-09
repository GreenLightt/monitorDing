<?php
namespace Redgo\MonitorDing\Middleware;

use Error;
use Closure;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Contracts\Container\Container;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Redgo\MonitorDing\MonitorDingClient;
use Symfony\Component\Debug\Exception\FatalThrowableError;

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
        $enabled = config('monitorDing.enabled');
        try {
            $response = $next($request);
        } catch (Exception $e) {
            $response = $this->handleException($request, $e);
            $enabled && $this->monitor->sendText(sprintf("文件：%s (%s 行) 内容：%s", $e->getFile(), $e->getLine(), $e->getMessage()));
        } catch (Error $error) {
            $e = new FatalThrowableError($error);
            $response = $this->handleException($request, $e);
            $enabled && $this->monitor->sendText(sprintf("文件：%s (%s 行) 内容：%s", $e->getFile(), $e->getLine(), $e->getMessage()));
        }

        return $response;
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
