<?php

namespace CrCms\Server\Drivers\Laravel\Http;

use Swoole\Http\Response as SwooleResponse;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response as BaseResponse;

/**
 * Class Response.
 */
class Response
{
    /**
     * @var SwooleResponse
     */
    protected $swooleResponse;

    /**
     * @var BaseResponse
     */
    protected $illuminateResponse;

    /**
     * Response constructor.
     *
     * @param SwooleResponse $response
     * @param BaseResponse   $illuminateResponse
     */
    public function __construct(SwooleResponse $response, BaseResponse $illuminateResponse)
    {
        $this->swooleResponse = $response;
        $this->illuminateResponse = $illuminateResponse;
    }

    /**
     * @param SwooleResponse $response
     * @param BaseResponse   $illuminateResponse
     *
     * @return Response
     */
    public static function make(SwooleResponse $response, BaseResponse $illuminateResponse)
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
