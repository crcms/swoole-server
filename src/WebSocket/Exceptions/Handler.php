<?php

namespace CrCms\Server\WebSocket\Exceptions;

use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;
use Psr\Log\LoggerInterface;
use Illuminate\Contracts\Container\Container;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Http\Exceptions\HttpResponseException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\Console\Application as ConsoleApplication;

/**
 * Class Handler
 * @package CrCms\Server\WebSocket\Exceptions
 */
class Handler implements ExceptionHandler
{
    /**
     * The container implementation.
     *
     * @var \Illuminate\Contracts\Container\Container
     */
    protected $container;

    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [];

    /**
     * A list of the internal exception types that should not be reported.
     *
     * @var array
     */
    protected $internalDontReport = [
        ModelNotFoundException::class,
        ValidationException::class,
    ];


    /**
     * Create a new exception handler instance.
     *
     * @param  \Illuminate\Contracts\Container\Container $container
     * @return void
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    public function report(Exception $e)
    {
        if ($this->shouldntReport($e)) {
            return;
        }

        if (method_exists($e, 'report')) {
            return $e->report();
        }

        try {
            $logger = $this->container->make(LoggerInterface::class);
        } catch (Exception $ex) {
            throw $e;
        }

        $logger->error(
            $e->getMessage(),
            ['exception' => $e]
        );
    }

    public function render($socket, Exception $e)
    {
        $data = [];

        if ($e instanceof HttpResponseException) {
            $data['message'] = $e->getResponse()->getContent();
            $data['status'] = $e->getResponse()->getStatusCode();
        } elseif ($e instanceof ValidationException) {
            $data = [
                'message' => $e->getMessage(),
                'errors' => $e->errors(),
                'status' => $e->status
            ];
        } else {
            $data = $this->convertExceptionToArray($e);
            $data['status'] = $this->isHttpException($e) ? $e->getStatusCode() : 500;
        }

        $socket->emit('error', $data);
    }

    public function renderForConsole($output, Exception $e)
    {
        (new ConsoleApplication)->renderException($e, $output);
    }

    /**
     * Determine if the exception is in the "do not report" list.
     *
     * @param  \Exception $e
     * @return bool
     */
    protected function shouldntReport(Exception $e)
    {
        $dontReport = array_merge($this->dontReport, $this->internalDontReport);

        return !is_null(Arr::first($dontReport, function ($type) use ($e) {
            return $e instanceof $type;
        }));
    }

    /**
     * Convert the given exception to an array.
     *
     * @param  \Exception $e
     * @return array
     */
    protected function convertExceptionToArray(Exception $e)
    {
        return config('app.debug') ? [
            'message' => $e->getMessage(),
            'exception' => get_class($e),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => collect($e->getTrace())->map(function ($trace) {
                return Arr::except($trace, ['args']);
            })->all(),
        ] : [
            'message' => $this->isHttpException($e) ? $e->getMessage() : 'Server Error',
        ];
    }

    /**
     * Determine if the given exception is an HTTP exception.
     *
     * @param  \Exception $e
     * @return bool
     */
    protected function isHttpException(Exception $e)
    {
        return $e instanceof HttpExceptionInterface;
    }
}
