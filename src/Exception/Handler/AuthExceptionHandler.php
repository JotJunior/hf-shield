<?php

declare(strict_types=1);

namespace Jot\HfOAuth2\Exception\Handler;

use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\ExceptionHandler\ExceptionHandler;
use Hyperf\HttpMessage\Stream\SwooleStream;
use Jot\HfOAuth2\Exception\MissingResourceScopeException;
use Jot\HfOAuth2\Exception\UnauthorizedAccessException;
use Jot\HfOAuth2\Exception\UnauthorizedClientException;
use Jot\HfOAuth2\Exception\UnauthorizedUserException;
use Psr\Http\Message\ResponseInterface;
use Throwable;

class AuthExceptionHandler extends ExceptionHandler
{
    public function __construct(protected StdoutLoggerInterface $logger)
    {
    }

    public function handle(Throwable $throwable, ResponseInterface $response)
    {
        if ($throwable instanceof MissingResourceScopeException ||
            $throwable instanceof UnauthorizedAccessException ||
            $throwable instanceof UnauthorizedUserException ||
            $throwable instanceof UnauthorizedClientException) {

            $this->stopPropagation();
            return $this->createJsonResponse($response, 401, ['message' => $throwable->getMessage()]);
        }

        return $response;
    }

    private function createJsonResponse(ResponseInterface $response, int $statusCode, array $data): ResponseInterface
    {
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus($statusCode)
            ->withBody(new SwooleStream(json_encode($data, JSON_UNESCAPED_UNICODE)));
    }

    public function isValid(Throwable $throwable): bool
    {
        return true;
    }
}
