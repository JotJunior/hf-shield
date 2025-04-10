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

use Hyperf\HttpServer\Router\Dispatched;
use Hyperf\HttpServer\Router\Handler;
use Jot\HfShield\AllowedScopes;
use Jot\HfShield\Exception\MissingResourceScopeException;
use Jot\HfShield\Exception\UnauthorizedAccessException;
use Jot\HfShield\Exception\UnauthorizedClientException;
use Jot\HfShield\Exception\UnauthorizedUserException;
use League\OAuth2\Server\Exception\OAuthServerException;
use Psr\Http\Message\ServerRequestInterface;

trait BearerTrait
{
    public const ATTR_ACCESS_TOKEN_ID = 'oauth_access_token_id';

    public const ATTR_CLIENT_ID = 'oauth_client_id';

    public const ATTR_USER_ID = 'oauth_user_id';

    public const ATTR_SCOPES = 'oauth_scopes';

    /**
     * Validates the bearer authentication strategy for the incoming request and ensures the presence of required scopes.
     *
     * This method authenticates the request using the OAuth server, collects the resource-specific scopes,
     * and verifies the request attributes. If authentication fails, an exception is thrown. Additionally,
     * it checks for the presence of required resource scopes and throws an exception if they are missing.
     *
     * @param ServerRequestInterface $request the incoming server request to be validated
     */
    protected function validateBearerStrategy(ServerRequestInterface $request): void
    {
        try {
            $this->request = $this->server->validateAuthenticatedRequest($request);
        } catch (OAuthServerException $e) {
            throw new UnauthorizedAccessException();
        }

        $this->collectResourceScopes();
        if (empty($this->resourceScopes)) {
            throw new MissingResourceScopeException();
        }
        $this->validateRequestAttributes();
    }

    /**
     * Gathers and assigns the resource scopes based on the dispatched route handler.
     */
    protected function collectResourceScopes(): void
    {
        $dispatched = $this->request->getAttribute(Dispatched::class);
        if ($dispatched instanceof Dispatched) {
            $routeHandler = $dispatched->handler;
            if ($routeHandler instanceof Handler) {
                $controller = $routeHandler->callback[0];
                $method = $routeHandler->callback[1];
                $this->resourceScopes = (array) AllowedScopes::get($controller, $method)->allow;
            }
        }
    }

    /**
     * Validates the attributes of an incoming request to ensure compliance with authorization requirements.
     *
     * This method performs a series of checks on the request attributes, including access token validity,
     * client verification, and user validation. If any of these checks fail, the corresponding exception is thrown.
     */
    protected function validateRequestAttributes(): void
    {
        $this->assertRequestAttribute(self::ATTR_ACCESS_TOKEN_ID, UnauthorizedAccessException::class);

        if (! $this->tokenHasRequiredScopes()) {
            throw new UnauthorizedAccessException();
        }

        $client = $this->repository->isClientValid(
            $this->request->getAttribute(self::ATTR_CLIENT_ID)
        );
        if (! $client) {
            throw new UnauthorizedClientException();
        }

        $userId = $this->request->getAttribute(self::ATTR_USER_ID);

        if (! $this->repository->isUserValid($userId, $client['tenant']['id'], $this->resourceScopes)) {
            throw new UnauthorizedUserException();
        }

        $this->request->withAttribute('oauth_user_session', $this->repository->getUserSessionData($userId));
    }

    /**
     * Validates that a specific attribute exists in the request.
     * If the attribute is missing or empty, an exception of the specified class is thrown.
     *
     * @param string $attributeName the name of the attribute to check in the request
     * @param string $exceptionClass the fully qualified class name of the exception to be thrown if the attribute is missing
     */
    protected function assertRequestAttribute(string $attributeName, string $exceptionClass): void
    {
        if (empty($this->request->getAttribute($attributeName))) {
            throw new $exceptionClass();
        }
    }

    /**
     * Checks if the provided token contains all the required scopes for resource access.
     *
     * This method compares the scopes associated with the token against the required resource scopes
     * to determine if they are fully satisfied. Access is granted only if all required scopes are present.
     *
     * @return bool true if the token contains all required scopes; otherwise, false
     */
    protected function tokenHasRequiredScopes(): bool
    {
        $tokenScopes = $this->request->getAttribute(self::ATTR_SCOPES);

        foreach ($this->resourceScopes as $resourceScope) {
            foreach ($tokenScopes as $tokenScope) {
                $scopeParts = explode(':', $tokenScope);

                if ($scopeParts < 3 && ! str_ends_with($tokenScope, ':')) {
                    return false;
                }

                if (str_starts_with($resourceScope, $tokenScope)) {
                    return true;
                }
            }
        }

        return false;
    }
}
