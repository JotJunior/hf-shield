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

use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\GetMapping;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Annotation\PostMapping;
use Jot\HfShield\Annotation\Scope;
use Jot\HfShield\AuthOption\Webauthn\Handler\WebauthnRegisterCreateOptionsHandler;
use Jot\HfShield\AuthOption\Webauthn\Handler\WebauthnRegisterCredentialsHandler;
use Jot\HfShield\Controller\AbstractController;
use Jot\HfShield\Middleware\SessionStrategy;
use Jot\HfShield\Repository\AccessTokenRepository;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\Serializer\SerializerInterface;

#[Controller(prefix: '/web-auth')]
class WebauthnRegisterController extends AbstractController
{
    use WebauthnRegisterCreateOptionsHandler;
    use WebauthnRegisterCredentialsHandler;

    private const CONTENT_TYPE_JSON = 'application/json';

    private const HTTP_STATUS_OK = 200;

    protected string $repository = AccessTokenRepository::class;

    #[Inject]
    protected SerializerInterface $serializer;

    #[Scope(allow: 'oauth:user:view')]
    #[Middleware(middleware: SessionStrategy::class)]
    #[GetMapping(path: 'challenge')]
    public function webauthnOptions(): ResponseInterface
    {
        $userId = $this->request->getAttribute('oauth_user_id');
        $user = $this->repository()->getUserSessionData($userId);

        $publicKeyCredentialCreationOptions = $this->createPublicKeyCredentialOptions($user);
        $this->storeChallenge($publicKeyCredentialCreationOptions, $user);

        return $this->createJsonResponse(
            $this->serializeToJson($publicKeyCredentialCreationOptions)
        );
    }

    #[Scope(allow: 'oauth:user:view')]
    #[Middleware(middleware: SessionStrategy::class)]
    #[PostMapping(path: 'register')]
    public function webauthnRegister(): ResponseInterface
    {
        $userId = $this->request->getAttribute('oauth_user_id');
        $user = $this->repository()->getUserSessionData($userId);

        $body = $this->request->getParsedBody();
        $credentials = $this->serializer->serialize(
            data: $body['credentials'],
            format: 'json'
        );

        $publicKeyCredential = $this->loadPublicKeyCredential($credentials);
        $userId = $this->request->getAttribute('oauth_user_id');
        $publicKeyCredentialSource = $this->checkAttestation($publicKeyCredential, $userId);
        $this->storeCredentials($publicKeyCredentialSource, $user);
        $this->updateUserTags($user);

        return $this->response->json([
            'status' => self::HTTP_STATUS_OK,
            'message' => 'OK',
        ]);
    }
}
