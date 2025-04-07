<?php

declare(strict_types=1);
/**
 * This file is part of the hf_shield module, a package build for Hyperf framework that is responsible for OAuth2 authentication and access control.
 *
 * @author   Joao Zanon <jot@jot.com.br>
 * @link     https://github.com/JotJunior/hf-shield
 * @license  MIT
 */

namespace Jot\HfShield\Exception\Handler;

use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\ExceptionHandler\ExceptionHandler;
use Hyperf\HttpMessage\Stream\SwooleStream;
use Jot\HfShield\Exception\MissingResourceScopeException;
use Jot\HfShield\Exception\UnauthorizedAccessException;
use Jot\HfShield\Exception\UnauthorizedClientException;
use Jot\HfShield\Exception\UnauthorizedSessionException;
use Jot\HfShield\Exception\UnauthorizedUserException;
use Psr\Http\Message\ResponseInterface;
use Throwable;

class AuthExceptionHandler extends ExceptionHandler
{
    public function __construct(protected StdoutLoggerInterface $logger)
    {
    }

    public function handle(Throwable $throwable, ResponseInterface $response)
    {
        if ($throwable instanceof MissingResourceScopeException
            || $throwable instanceof UnauthorizedAccessException
            || $throwable instanceof UnauthorizedSessionException
            || $throwable instanceof UnauthorizedUserException
            || $throwable instanceof UnauthorizedClientException) {
            $this->stopPropagation();
            return $this->createJsonResponse($response, 401, ['message' => $throwable->getMessage()]);
        }

        return $response;
    }

    public function isValid(Throwable $throwable): bool
    {
        return true;
    }

    private function createJsonResponse(ResponseInterface $response, int $statusCode, array $data): ResponseInterface
    {
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus($statusCode)
            ->withBody(new SwooleStream(json_encode($data, JSON_UNESCAPED_UNICODE)));
    }
}
