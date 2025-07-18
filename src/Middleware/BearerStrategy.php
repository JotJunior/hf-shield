<?php

declare(strict_types=1);
/**
 * This file is part of the hf_shield module, a package build for Hyperf framework that is responsible for OAuth2 authentication and access control.
 *
 * @author   Joao Zanon <jot@jot.com.br>
 * @link     https://github.com/JotJunior/hf-shield
 * @license  MIT
 */

namespace Jot\HfShield\Middleware;

use Jot\HfShield\Repository\AccessTokenRepository;
use League\OAuth2\Server\ResourceServer;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class BearerStrategy implements MiddlewareInterface
{
    use BearerTrait;

    public function __construct(
        protected ContainerInterface $container,
        protected ResourceServer $server,
        protected AccessTokenRepository $repository,
        protected ServerRequestInterface $request,
        protected array $resourceScopes = []
    ) {
    }

    /**
     * Processes the incoming request, validates its attributes, and forwards it to the specified handler.
     *
     * This method authenticates the request, collects resource scopes, and performs validation to ensure
     * that the request complies with the necessary authorization and scope requirements. In case of any
     * failure during validation or missing scopes, the appropriate exceptions are thrown.
     *
     * @param ServerRequestInterface $request the incoming server request to be processed
     * @param RequestHandlerInterface $handler the handler to forward the validated request to
     * @return ResponseInterface the response generated by the handler after processing the request
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $this->validateBearerStrategy($request);
        $this->logRequest();
        return $handler->handle(
            $this->request
                ->withAttribute('oauth_session_user', $this->getOauthUser())
                ->withQueryParams([...$this->request->getQueryParams(), '_tenant_id' => $this->getOauthUser()['tenant']['id'], '_user_id' => $this->getOauthUser()['id']])
        );
    }
}
