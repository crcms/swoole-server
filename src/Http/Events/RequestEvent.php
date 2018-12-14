<?php

namespace CrCms\Server\Http\Events;

use Carbon\Carbon;
use CrCms\Server\Http\Request;
use CrCms\Server\Server\AbstractServer;
use CrCms\Server\Server\Contracts\EventContract;
use CrCms\Server\Server\Events\AbstractEvent;
use Illuminate\Http\Response as IlluminateResponse;
use Swoole\Http\Request as SwooleRequest;
use Swoole\Http\Response as SwooleResponse;
use Illuminate\Http\Request as IlluminateRequest;
use Illuminate\Contracts\Http\Kernel;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class RequestEvent
 * @package CrCms\Server\Http\Events
 */
class RequestEvent extends AbstractEvent implements EventContract
{
    /**
     * @var SwooleRequest
     */
    protected $request;

    /**
     * @var SwooleResponse
     */
    protected $response;

    /**
     * @var IlluminateRequest
     */
    protected $illuminateRequest;

    /**
     * @var IlluminateResponse
     */
    protected $illuminateResponse;

    /**
     * @var Kernel
     */
    protected $kernel;

    /**
     * Request constructor.
     * @param SwooleRequest $request
     * @param SwooleResponse $response
     */
    public function __construct(SwooleRequest $request, SwooleResponse $response)
    {
        $this->request = $request;
        $this->response = $response;
    }

    /**
     * @return void
     */
    public function handle(AbstractServer $server): void
    {
        parent::handle($server);

        $this->kernel = $server->getApplication()->make(Kernel::class);
        $this->illuminateRequest = new Request($this->request);
        $this->illuminateResponse = $this->createIlluminateResponse();

        //$this->requestLog();

        $this->setResponse();
    }

    /**
     *
     */
    protected function setResponse()
    {
        $this->response->status($this->illuminateResponse->getStatusCode());

        foreach ($this->illuminateResponse->headers->allPreserveCaseWithoutCookies() as $key => $value) {
            $this->response->header($key, implode(';', $value));
        }

        foreach ($this->illuminateResponse->headers->getCookies() as $cookie) {
            $this->response->cookie(
                $cookie->getName(),
                $cookie->getValue(),
                $cookie->getExpiresTime(),
                $cookie->getPath(),
                $cookie->getDomain(),
                $cookie->isSecure(),
                $cookie->isHttpOnly()
            );
        }

        //$this->response->gzip(1);

        $this->response->end($this->illuminateResponse->getContent());

        $this->kernel->terminate($this->illuminateRequest, $this->illuminateResponse);
    }

    /**
     * @return IlluminateResponse
     */
    protected function createIlluminateResponse(): Response
    {
        return $this->kernel->handle($this->illuminateRequest);
    }

    /**
     *
     */
    protected function requestLog()
    {
        $params = http_build_query($this->illuminateRequest->all());
        $currentTime = Carbon::now()->toDateTimeString();
        $header = http_build_query($this->illuminateRequest->headers->all());

        $requestTime = Carbon::createFromTimestamp($this->illuminateRequest->server('REQUEST_TIME'));
        $content = "RecordTime:{$currentTime} RequestTime:{$requestTime} METHOD:{$this->illuminateRequest->method()} IP:{$this->illuminateRequest->ip()} Params:{$params} Header:{$header}" . PHP_EOL;

        $this->server->getProcess()->write($content);
    }
}