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
use Jot\HfShield\Exception\MissingResourceScopeException;
use Jot\HfShield\Exception\UnauthorizedAccessException;
use Jot\HfShield\Exception\UnauthorizedClientException;
use Jot\HfShield\Exception\UnauthorizedSessionException;
use Jot\HfShield\Exception\UnauthorizedUserException;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;
use Throwable;

class AuthExceptionHandler extends ExceptionHandler
{
    private LoggerInterface $logger;

    public function __construct(LoggerFactory $loggerFactory)
    {
        $this->logger = $loggerFactory->get('auth', 'elastic');
    }

    public function handle(Throwable $throwable, ResponseInterface $response): ResponseInterface
    {
        if ($throwable instanceof MissingResourceScopeException
            || $throwable instanceof UnauthorizedAccessException
            || $throwable instanceof UnauthorizedSessionException
            || $throwable instanceof UnauthorizedUserException
            || $throwable instanceof UnauthorizedClientException) {
            $this->stopPropagation();
            $context = $throwable->getMetadata();
            $context['exception'] = [
                'message' => $throwable->getMessage(),
                'file' => $throwable->getFile(),
                'line' => $throwable->getLine(),
                'trace' => $throwable->getTraceAsString(),
            ];
            $this->logger->error($throwable->getMessage(), $context);
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
