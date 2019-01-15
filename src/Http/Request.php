<?php

namespace CrCms\Server\Http;

use Illuminate\Http\Request as IlluminateRequest;
use Illuminate\Support\Collection;
use Swoole\Http\Request as SwooleRequest;
use Symfony\Component\HttpFoundation\ParameterBag;

/**
 * Class Request.
 */
class Request
{
    /**
     * @var SwooleRequest
     */
    protected $swooleRequest;

    /**
     * @var IlluminateRequest
     */
    protected $illuminateRequest;

    /**
     * Request constructor.
     *
     * @param SwooleRequest $request
     */
    public function __construct(SwooleRequest $request)
    {
        $this->swooleRequest = $request;

        $this->illuminateRequest = $this->createIlluminateRequest();
    }

    /**
     * @param SwooleRequest $request
     *
     * @return Request
     */
    public static function make(SwooleRequest $request)
    {
        return new static($request);
    }

    /**
     * @return IlluminateRequest
     */
    public function getIlluminateRequest(): IlluminateRequest
    {
        return $this->illuminateRequest;
    }

    /**
     * @return IlluminateRequest
     */
    protected function createIlluminateRequest(): IlluminateRequest
    {
        $request = new IlluminateRequest(
            $this->swooleRequest->get ?? [],
            $this->mergePostData(),
            [],
            $this->swooleRequest->cookie ?? [],
            $this->swooleRequest->files ?? [],
            $this->mergeServerInfo(), $this->swooleRequest->rawContent()
        );

        if (0 === strpos($request->headers->get('CONTENT_TYPE'), 'application/x-www-form-urlencoded')
            && in_array(strtoupper($request->server->get('REQUEST_METHOD', 'GET')), ['PUT', 'DELETE', 'PATCH'])
        ) {
            parse_str($request->getContent(), $data);
            $request->request = new ParameterBag($data);
        }

        return $request;
    }

    /**
     * @return array
     */
    protected function mergePostData(): array
    {
        $data = [];

        if (strtoupper($this->swooleRequest->server['request_method']) === 'POST') {
            $data = empty($this->swooleRequest->post) ? [] : $this->swooleRequest->post;

            if (isset($this->swooleRequest->header['content-type']) && stripos($this->swooleRequest->header['content-type'], 'application/json') !== false) {
                $content = $this->swooleRequest->rawContent();
                if ($content) {
                    $content = json_decode($content, true);
                    if (json_last_error() === 0) {
                        $data = array_merge($data, $content);
                    }
                }
            }
        }

        return $data;
    }

    /**
     * @return array
     */
    protected function mergeServerInfo(): array
    {
        $server = $_SERVER;
        if ('cli-server' === PHP_SAPI) {
            if (array_key_exists('HTTP_CONTENT_LENGTH', $_SERVER)) {
                $server['CONTENT_LENGTH'] = $_SERVER['HTTP_CONTENT_LENGTH'];
            }
            if (array_key_exists('HTTP_CONTENT_TYPE', $_SERVER)) {
                $server['CONTENT_TYPE'] = $_SERVER['HTTP_CONTENT_TYPE'];
            }
        }

        $requestHeader = Collection::make($this->swooleRequest->header)->mapWithKeys(function ($item, $key) {
            $key = str_replace('-', '_', $key);

            return in_array(strtolower($key), ['x_real_ip'], true) ?
                [$key => $item] :
                ['http_'.$key => $item];
        })->toArray();

        $server = array_merge($server, $this->swooleRequest->server, $requestHeader);

        return array_change_key_case($server, CASE_UPPER);
    }
}
