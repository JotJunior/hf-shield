<?php

declare(strict_types=1);
/**
 * This file is part of the hf_shield module, a package build for Hyperf framework that is responsible for OAuth2 authentication and access control.
 *
 * @author   Joao Zanon <jot@jot.com.br>
 * @link     https://github.com/JotJunior/hf-shield
 * @license  MIT
 */

namespace Jot\HfShield\AuthOption\WhatsApp\Controller;

use DateTime;
use Exception;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\PostMapping;
use Hyperf\HttpServer\Annotation\RequestMapping;
use Jot\HfShield\AuthOption\SessionToken\Controller\SessionTokenOauthController;
use Jot\HfShield\AuthOption\WhatsApp\Handler\AccessTokenHandler;
use Jot\HfShield\Exception\UnauthorizedAccessException;
use Jot\HfShield\Repository\AccessTokenRepository;
use Jot\HfShield\Repository\UserRepository;
use Jot\HfShield\Service\OtpService;
use Psr\Http\Message\ResponseInterface as PsrResponseInterface;

#[Controller(prefix: '/whatsapp/login')]
class WhatsAppLoginController extends SessionTokenOauthController
{
    use AccessTokenHandler;

    private const CONTENT_TYPE_JSON = 'application/json';

    private const HTTP_STATUS_OK = 200;

    protected string $repository = AccessTokenRepository::class;

    #[Inject]
    protected OtpService $otpService;

    #[Inject]
    protected UserRepository $userRepository;

    #[PostMapping(path: 'start')]
    public function sendOtpCode(): PsrResponseInterface
    {
        if (! in_array('whatsapp', $this->config['auth_options'])) {
            throw new UnauthorizedAccessException();
        }

        $body = $this->request->getParsedBody();

        return $this->response
            ->json($this->otpService->create($body));
    }

    #[PostMapping(path: 'token')]
    public function otpReturnsToken(): PsrResponseInterface
    {
        return $this->response
            ->withAddedHeader('Content-Type', 'application/json')
            ->json([
                'access_token' => $this->createToken(),
                'token_type' => 'Bearer',
            ]);
    }

    #[PostMapping(path: 'cookie')]
    public function otpReturnsCookie(): PsrResponseInterface
    {
        if (! in_array('whatsapp', $this->config['auth_options'])) {
            throw new UnauthorizedAccessException();
        }

        $sessionConfig = $this->configService->get('hf_session');

        $body = $this->request->getParsedBody();
        $body['client_id'] = $sessionConfig['auth_settings']['client_id'];
        $body['scope'] = $sessionConfig['auth_settings']['scopes'];

        try {
            $cookie = $this->buildAccessTokenCookie(
                accessToken: $this->createToken(),
                expiresIn: (new DateTime('+1 day'))->getTimestamp() - time()
            );
        } catch (\Throwable $th) {
            return $this->response
                ->redirect($sessionConfig['redirect_uri'] . '?error=' . $th->getMessage());
        }

        $this->log('hf_shield.logged_in');

        if (empty($sessionConfig)) {
            throw new UnauthorizedAccessException();
        }

        return $this->response
            ->withAddedHeader('Set-Cookie', (string) $cookie)
            ->redirect($sessionConfig['redirect_uri']);
    }

    #[RequestMapping(path: '/whatsapp/login/start', methods: ['OPTIONS'])]
    public function requestOptionsValue(): PsrResponseInterface
    {
        return $this->response
            ->json([
                'methods' => ['POST'],
                'rate_limit' => 'Max 10 requests per second.',
            ]);
    }

    private function createToken(): string
    {
        if (! in_array('whatsapp', $this->config['auth_options'])) {
            throw new UnauthorizedAccessException();
        }

        $body = $this->request->getParsedBody();

        $validOtp = $this->otpService->validateCode($body);

        if ($validOtp['result'] !== 'success') {
            throw new Exception($validOtp['message'], 400);
        }

        $otp = $this->otpService->getOtp($validOtp['data']);
        $this->otpService->changeOtpStatus($otp, OtpService::OTP_STATUS_COMPLETE);

        $body['user'] = $otp->user->toArray();
        return $this->issueTokenString($body);
    }
}
