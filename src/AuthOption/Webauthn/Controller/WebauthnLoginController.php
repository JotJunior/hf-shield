<?php

declare(strict_types=1);
/**
 * This file is part of the hf_shield module, a package build for Hyperf framework that is responsible for OAuth2 authentication and access control.
 *
 * @author   Joao Zanon <jot@jot.com.br>
 * @link     https://github.com/JotJunior/hf-shield
 * @license  MIT
 */

namespace Jot\HfShield\AuthOption\Webauthn\Controller;

use DateTime;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\PostMapping;
use Hyperf\HttpServer\Annotation\RequestMapping;
use Jot\HfShield\Annotation\Scope;
use Jot\HfShield\AuthOption\SessionToken\Controller\SessionTokenOauthController;
use Jot\HfShield\AuthOption\Webauthn\Handler\WebauthnLoginCollectCredentialsHandler;
use Jot\HfShield\AuthOption\Webauthn\Handler\WebauthnLoginValidateCredentialsHandler;
use Jot\HfShield\Exception\UnauthorizedAccessException;
use Jot\HfShield\Exception\UnauthorizedUserException;
use Jot\HfShield\Repository\AccessTokenRepository;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ResponseInterface as PsrResponseInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Throwable;

#[Controller(prefix: '/web-auth/login')]
class WebauthnLoginController extends SessionTokenOauthController
{
    use WebauthnLoginCollectCredentialsHandler;
    use WebauthnLoginValidateCredentialsHandler;

    private const CONTENT_TYPE_JSON = 'application/json';

    private const HTTP_STATUS_OK = 200;

    protected string $repository = AccessTokenRepository::class;

    #[Inject]
    protected SerializerInterface $serializer;

    #[Scope(allow: 'oauth:user:view')]
    #[PostMapping(path: 'collect')]
    public function webauthnRequestOptions(): ResponseInterface
    {
        return $this->createJsonResponse(
            $this->serializeToJson(
                $this->getPublicKeyCredentialRequestOptions($this->request->all())
            )
        );
    }

    #[Scope(allow: 'oauth:user:view')]
    #[PostMapping(path: 'validate')]
    public function webauthnAuth(): ResponseInterface
    {
        if (! in_array('webauthn', $this->config['auth_options'])) {
            throw new UnauthorizedAccessException();
        }

        $body = $this->request->getParsedBody();
        $credentials = $this->serializer->serialize(
            data: $body['credentials'],
            format: 'json'
        );

        $publicKeyCredential = $this->loadPublicKeyCredential($credentials);

        try {
            $this->validateAssertion($publicKeyCredential);
        } catch (Throwable $e) {
            throw new UnauthorizedUserException();
        }

        $token = $this->issueTokenString($publicKeyCredential->response->userHandle);

        $cookie = $this->buildAccessTokenCookie($token, (new DateTime('+1 day'))->getTimestamp() - time());

        return $this->response
            ->withAddedHeader('Set-Cookie', (string) $cookie)
            ->json([
                'status' => 200,
                'message' => 'ok',
                'redirect_uri' => $this->configService->get('hf_session.redirect_uri'),
            ]);
    }

    #[RequestMapping(path: '/web-auth/login/collect', methods: ['OPTIONS'])]
    public function requestOptionsValue(): PsrResponseInterface
    {
        return $this->response
            ->json([
                'methods' => ['POST'],
                'rate_limit' => 'Max 10 requests per second.',
            ]);
    }
}
