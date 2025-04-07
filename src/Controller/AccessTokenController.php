<?php

declare(strict_types=1);
/**
 * This file is part of the hf_shield module, a package build for Hyperf framework that is responsible for OAuth2 authentication and access control.
 *
 * @author   Joao Zanon <jot@jot.com.br>
 * @link     https://github.com/JotJunior/hf-shield
 * @license  MIT
 */

namespace Jot\HfShield\Controller;

use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\DeleteMapping;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\RateLimit\Annotation\RateLimit;
use Hyperf\Swagger\Annotation as SA;
use Jot\HfShield\Exception\UnauthorizedAccessException;
use Jot\HfShield\Middleware\BearerStrategy;
use Jot\HfShield\Repository\AccessTokenRepository;
use League\OAuth2\Server\Exception\OAuthServerException;
use Psr\Http\Message\ResponseInterface as PsrResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Throwable;

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
class AccessTokenController extends AbstractController
{
    protected string $repository = AccessTokenRepository::class;

    #[SA\Post(
        path: '/oauth/token',
        description: 'Create a new user token.',
        summary: 'Create a new user token',
        requestBody: new SA\RequestBody(
            required: true,
            content: new SA\JsonContent(ref: '#/components/schemas/oauth.access-token.request')
        ),
        tags: ['JWT Access token'],
        responses: [
            new SA\Response(
                response: 200,
                description: 'Access token created',
                content: new SA\JsonContent(ref: '#/components/schemas/oauth.access-token.response')
            ),
            new SA\Response(
                response: 400,
                description: 'Bad request',
                content: new SA\JsonContent(ref: '#/components/schemas/jot.hf-shield.auth-error.response')
            ),
            new SA\Response(
                response: 401,
                description: 'Unauthorized access',
                content: new SA\JsonContent(ref: '#/components/schemas/jot.hf-shield.auth-error.response')
            ),
            new SA\Response(
                response: 500,
                description: 'Application error',
                content: new SA\JsonContent(ref: '#/components/schemas/jot.hf-shield.auth-error.response')
            ),
        ]
    )]
    #[RateLimit(create: 1, capacity: 2)]
    public function issueToken(RequestInterface $request): PsrResponseInterface
    {
        try {
            return $this->server->respondToAccessTokenRequest($request, $this->response);
        } catch (OAuthServerException $e) {
            return $this->response
                ->withStatus($e->getHttpStatusCode())
                ->json([
                    'error' => $e->getMessage(),
                    'status_code' => $e->getHttpStatusCode(),
                ]);
        } catch (Throwable $e) {
            return $this->response
                ->withStatus(401)
                ->json([
                    'error' => $e->getMessage(),
                    'class' => get_class($e),
                    'trace' => $e->getTrace(),
                ]);
        }
    }

    #[DeleteMapping(path: 'token/{id}')]
    #[RateLimit(create: 1, capacity: 2)]
    #[Middleware(BearerStrategy::class)]
    public function revokeToken($id, ServerRequestInterface $request): PsrResponseInterface
    {
        if ($request->getAttribute(BearerStrategy::ATTR_USER_ID) !== $id) {
            throw new UnauthorizedAccessException();
        }
        $this->repository()->revokeAccessToken($id);
        return $this->response->withStatus(204)->raw('');
    }
}
