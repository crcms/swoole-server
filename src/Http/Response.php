<?php

namespace CrCms\Server\Http;

use Swoole\Http\Response as SwooleResponse;
use Illuminate\Http\Response as IlluminateResponse;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * Class Response
 * @package CrCms\Server\Http
 */
class Response
{
    /**
     * @var SwooleResponse
     */
    protected $swooleResponse;

    /**
     * @var IlluminateResponse
     */
    protected $illuminateResponse;

    /**
     * Response constructor.
     * @param SwooleResponse $response
     * @param IlluminateResponse $illuminateResponse
     */
    public function __construct(SwooleResponse $response, IlluminateResponse $illuminateResponse)
    {
        $this->swooleResponse = $response;
        $this->illuminateResponse = $illuminateResponse;
    }

    /**
     * @param SwooleResponse $response
     * @param IlluminateResponse $illuminateResponse
     * @return Response
     */
    public static function make(SwooleResponse $response, IlluminateResponse $illuminateResponse)
    {
        return new static($response, $illuminateResponse);
    }

    /**
     * @return void
     */
    public function toResponse(): void
    {
        $this->sendHeaders();
        $this->sendContent();
    }

    /**
     * @return void
     */
    protected function sendHeaders(): void
    {
        $this->swooleResponse->status($this->illuminateResponse->getStatusCode());
        foreach ($this->illuminateResponse->headers->allPreserveCaseWithoutCookies() as $key => $value) {
            $this->swooleResponse->header($key, implode(';', $value));
        }

        foreach ($this->illuminateResponse->headers->getCookies() as $cookie) {
            $this->swooleResponse->cookie(
                $cookie->getName(),
                $cookie->getValue(),
                $cookie->getExpiresTime(),
                $cookie->getPath(),
                $cookie->getDomain(),
                $cookie->isSecure(),
                $cookie->isHttpOnly()
            );
        }
    }

    /**
     * @return void
     */
    protected function sendContent(): void
    {
        if ($this->illuminateResponse instanceof BinaryFileResponse) {
            $this->swooleResponse->sendfile($this->illuminateResponse->getFile()->getPathname());
        } else {
            $this->swooleResponse->end($this->illuminateResponse->getContent());
        }
    }
}