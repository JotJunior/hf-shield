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

use Hyperf\ExceptionHandler\ExceptionHandler;
use Hyperf\HttpMessage\Stream\SwooleStream;
use Hyperf\Logger\LoggerFactory;
use Jot\HfShield\Exception\ForbiddenAccessException;
use Jot\HfShield\Exception\MissingResourceScopeException;
use Jot\HfShield\Exception\RecordNotFoundException;
use Jot\HfShield\Exception\UnauthorizedAccessException;
use Jot\HfShield\Exception\UnauthorizedClientException;
use Jot\HfShield\Exception\UnauthorizedSessionException;
use Jot\HfShield\Exception\UnauthorizedUserException;
use Jot\HfShield\LoggerContextCollector;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Throwable;

class AuthExceptionHandler extends ExceptionHandler
{
    use LoggerContextCollector;

    public function __construct(
        private readonly ServerRequestInterface $request,
        LoggerFactory                           $loggerFactory
    )
    {
        $this->setLogger($loggerFactory->get('auth', 'elastic'));
    }

    public function handle(
        Throwable         $throwable,
        ResponseInterface $response
    ): ResponseInterface
    {
        if (method_exists($throwable, 'getMetadata')) {
            $context = $throwable->getMetadata();
            $context['exception'] = [
                'message' => $throwable->getMessage(),
                'file' => $throwable->getFile(),
                'line' => $throwable->getLine(),
                'trace' => $throwable->getTraceAsString(),
            ];
            $throwable->setMetadata($context);
        }

        if ($throwable instanceof MissingResourceScopeException
            || $throwable instanceof UnauthorizedAccessException
            || $throwable instanceof UnauthorizedSessionException
            || $throwable instanceof UnauthorizedUserException
            || $throwable instanceof ForbiddenAccessException
            || $throwable instanceof UnauthorizedClientException) {
            $this->stopPropagation();
            $this->logError($throwable);
            return $this->createJsonResponse(
                response: $response,
                statusCode: $throwable->getCode(),
                data: [
                    'data' => null,
                    'result' => 'error',
                    'message' => $throwable->getMessage(),
                ]
            );
        }

        return $response;
    }

    private function logError(Throwable $throwable): void
    {
        $this->log($throwable->getMessage());
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
