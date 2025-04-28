<?php

declare(strict_types=1);
/**
 * This file is part of the hf_shield module, a package build for Hyperf framework that is responsible for OAuth2 authentication and access control.
 *
 * @author   Joao Zanon <jot@jot.com.br>
 * @link     https://github.com/JotJunior/hf-shield
 * @license  MIT
 */

namespace Jot\HfShield\AuthOption\SessionToken\Controller;

use Hyperf\HttpMessage\Cookie\Cookie;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;
use Hyperf\RateLimit\Annotation\RateLimit;
use Hyperf\Swagger\Annotation as SA;
use Jot\HfShield\Controller\AbstractController;
use Jot\HfShield\Exception\UnauthorizedSessionException;
use Jot\HfShield\Repository\AccessTokenRepository;
use League\OAuth2\Server\CryptTrait;
use League\OAuth2\Server\Exception\OAuthServerException;
use Psr\Http\Message\ResponseInterface as PsrResponseInterface;

#[SA\HyperfServer('http')]
#[SA\Schema(
    schema: 'oauth.access-token.request',
    required: ['username', 'password', 'client_id', 'client_secret', 'grant_type', 'scope'],
    properties: [
        new SA\Property(property: 'username', type: 'string', example: 'my@user.com'),
        new SA\Property(property: 'password', type: 'string', example: 'S3curit!PaSS'),
        new SA\Property(property: 'client_id', type: 'string', example: '0b3baa41-a10f-474a-abed-dc07c6236989'),
        new SA\Property(property: 'client_secret', type: 'string', example: 'd9bb586b-2b25-4007-830e-ef06f133a272'),
        new SA\Property(property: 'grant_type', type: 'string', example: 'password'),
        new SA\Property(property: 'scope', type: 'string', example: 'blog:content:read blog.content:create'),
    ],
    type: 'object'
)]
#[SA\Schema(
    schema: 'oauth.access-token.response',
    required: ['token_type', 'expires_in', 'access_token', 'refresh_token'],
    properties: [
        new SA\Property(property: 'token_type', type: 'string', example: 'Bearer'),
        new SA\Property(property: 'expires_in', type: 'integer', example: 86400),
        new SA\Property(property: 'access_token', type: 'string', example: ''),
        new SA\Property(property: 'refresh_token', type: 'string', example: ''),
    ],
    type: 'object'
)]
#[Controller(prefix: '/oauth')]
class SessionTokenController extends AbstractController
{
    use CryptTrait;

    private const AUTH_ERROR_RESPONSE_SCHEMA = '#/components/schemas/jot.hf-shield.auth-error.response';

    private const AUTH_TOKEN_RESPONSE_SCHEMA = '#/components/schemas/oauth.access-token.response';

    private const AUTH_TOKEN_REQUEST_SCHEMA = '#/components/schemas/oauth.access-token.request';

    protected string $repository = AccessTokenRepository::class;

    #[SA\Post(
        path: '/oauth/session',
        description: 'Create a new user token.',
        summary: 'Create a new user token',
        requestBody: new SA\RequestBody(
            required: true,
            content: new SA\JsonContent(ref: self::AUTH_TOKEN_REQUEST_SCHEMA)
        ),
        tags: ['JWT Access token'],
        responses: [
            new SA\Response(
                response: 200,
                description: 'Access token created',
                content: new SA\JsonContent(ref: self::AUTH_TOKEN_RESPONSE_SCHEMA)
            ),
            new SA\Response(
                response: 400,
                description: 'Bad request',
                content: new SA\JsonContent(ref: self::AUTH_ERROR_RESPONSE_SCHEMA)
            ),
            new SA\Response(
                response: 401,
                description: 'Unauthorized access',
                content: new SA\JsonContent(ref: self::AUTH_ERROR_RESPONSE_SCHEMA)
            ),
            new SA\Response(
                response: 500,
                description: 'Application error',
                content: new SA\JsonContent(ref: self::AUTH_ERROR_RESPONSE_SCHEMA)
            ),
        ]
    )]
    #[RateLimit(create: 1, capacity: 2)]
    public function issueToken(RequestInterface $request): PsrResponseInterface
    {
        $sessionConfig = $this->configService->get('hf_session');
        $this->validateSessionConfig($sessionConfig);

        $body = $this->prepareRequestBody($request->getParsedBody(), $sessionConfig);
        $request = $request->withParsedBody($body);

        try {
            $response = $this->server->respondToAccessTokenRequest($request, $this->response);
        } catch (OAuthServerException $e) {
            return $this->response
                ->redirect(sprintf('%s?err=%s&msg=%s', $sessionConfig['redirect_error'], $e->getCode(), $e->getMessage()));
        }

        $token = json_decode($response->getBody()->getContents(), true);
        $cookie = $this->buildAccessTokenCookie($token['access_token'], $token['expires_in']);

        return $response
            ->withAddedHeader('Set-Cookie', (string) $cookie)
            ->redirect($sessionConfig['redirect_uri']);
    }

    #[SA\Post(
        path: '/oauth/logout',
        description: 'Remove the session cookie.',
        summary: 'Remove the user session cookie',
        requestBody: null,
        tags: ['Session cookie'],
        responses: [
            new SA\Response(
                response: 200,
                description: 'Session logged out',
                content: new SA\JsonContent(ref: self::AUTH_TOKEN_RESPONSE_SCHEMA)
            ),
            new SA\Response(
                response: 400,
                description: 'Bad request',
                content: new SA\JsonContent(ref: self::AUTH_ERROR_RESPONSE_SCHEMA)
            ),
            new SA\Response(
                response: 500,
                description: 'Application error',
                content: new SA\JsonContent(ref: self::AUTH_ERROR_RESPONSE_SCHEMA)
            ),
        ]
    )]
    #[RateLimit(create: 1, capacity: 2)]
    public function logout(RequestInterface $request): PsrResponseInterface
    {
        $sessionConfig = $this->configService->get('hf_session');
        $response = $this->container->get(ResponseInterface::class);
        $cookie = $this->buildAccessTokenCookie('revoked', -1);
        return $response
            ->withAddedHeader('Set-Cookie', (string) $cookie)
            ->redirect($sessionConfig['redirect_login']);
    }

    /**
     * Creates and returns a cookie containing the access token with specified expiration and security attributes.
     *
     * @param string $accessToken the access token to be stored in the cookie
     * @param int $expiresIn the lifespan of the cookie in seconds
     * @return Cookie the constructed cookie instance with access token data
     */
    protected function buildAccessTokenCookie(string $accessToken, int $expiresIn): Cookie
    {
        return new Cookie(
            name: 'access_token',
            value: $this->encrypt($accessToken),
            expire: time() + $expiresIn,
            path: '/',
            secure: true,
            httpOnly: true,
            sameSite: 'Strict'
        );
    }

    /**
     * Validates the session configuration to ensure the required authentication settings are present.
     *
     * @param array $sessionConfig the session configuration array containing authentication settings
     * @throws UnauthorizedSessionException if the 'client_id' is missing from the authentication settings
     */
    private function validateSessionConfig(array $sessionConfig): void
    {
        if (empty($sessionConfig['auth_settings']['client_id'])) {
            throw new UnauthorizedSessionException();
        }
    }

    /**
     * Prepares and populates the request body with necessary authentication parameters.
     *
     * @param array $body the initial request body to be populated
     * @param array $sessionConfig the session configuration array containing authentication settings
     * @return array the modified request body with authentication details added
     */
    private function prepareRequestBody(array $body, array $sessionConfig): array
    {
        $authSettings = $sessionConfig['auth_settings'];
        $body['client_id'] = $authSettings['client_id'];
        $body['client_secret'] = $authSettings['client_secret'];
        $body['scope'] = $authSettings['scopes'];
        $body['grant_type'] = $authSettings['grant_type'];
        return $body;
    }
}
