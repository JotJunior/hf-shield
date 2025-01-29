<?php

namespace Jot\HfOAuth2\Middleware;

use Hyperf\HttpMessage\Stream\SwooleStream;

use Hyperf\HttpServer\Contract\ResponseInterface;
use Hyperf\RateLimit\Exception\RateLimitException;
use Psr\Http\Message\ResponseInterface as PsrResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Throwable;

class ExceptionHandlerMiddleware
{
    protected ResponseInterface $response;

    public function __construct(ResponseInterface $response)
    {
        $this->response = $response;
    }

    public function process(ServerRequestInterface $request, callable $handler): PsrResponseInterface
    {
        try {
            return $handler($request);
        } catch (RateLimitException $e) {
            return $this->response->withStatus(429) // Código HTTP "Too Many Requests"
            ->withBody(new SwooleStream(json_encode([
                'error' => 'Too Many Requests',
                'message' => $e->getMessage(),
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)));
        } catch (Throwable $e) {
            return $this->response->withStatus(500)
                ->withBody(new SwooleStream(json_encode([
                    'error' => 'Internal Server Error',
                    'message' => $e->getMessage(),
                ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)));
        }
    }
}