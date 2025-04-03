<?php

declare(strict_types=1);
/**
 * This file is part of hf-shield.
 *
 * @link     https://github.com/JotJunior/hf-shield
 * @contact  hf-shield@jot.com.br
 * @license  MIT
 */

namespace Jot\HfShield;

use DateInterval;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Di\Annotation\Inject;
use Jot\HfShield\Repository\AccessTokenRepository;
use Jot\HfShield\Repository\AuthCodeRepository;
use Jot\HfShield\Repository\ClientRepository;
use Jot\HfShield\Repository\RefreshTokenRepository;
use Jot\HfShield\Repository\ScopeRepository;
use Jot\HfShield\Repository\UserRepository;
use League\OAuth2\Server\AuthorizationServer;
use League\OAuth2\Server\CryptKey;
use League\OAuth2\Server\Grant\AuthCodeGrant;
use League\OAuth2\Server\Grant\ClientCredentialsGrant;
use League\OAuth2\Server\Grant\ImplicitGrant;
use League\OAuth2\Server\Grant\PasswordGrant;
use League\OAuth2\Server\Grant\RefreshTokenGrant;
use Psr\Container\ContainerInterface;

use function Hyperf\Support\make;
use function Hyperf\Tappable\tap;

class AuthorizationServerFactory
{
    #[Inject]
    protected ContainerInterface $container;

    #[Inject]
    protected ConfigInterface $config;

    protected ?DateInterval $tokenExpireDays = null;

    protected ?DateInterval $refreshTokenExpireDays = null;

    protected bool $implicitGrantEnabled = false;

    protected string $defaultScope = 'default';

    /**
     * Constructor method to initialize the class with dependency injection and configuration settings.
     *
     * @param ContainerInterface $container the service container instance
     * @param ConfigInterface $config the configuration instance providing necessary settings
     *
     * @throws DateMalformedIntervalStringException
     */
    public function __construct(ContainerInterface $container, ConfigInterface $config)
    {
        $this->container = $container;
        $this->config = $config;
        $this->tokenExpireDays = new DateInterval($this->config->get('hf_shield.token_days', 'P1D'));
        $this->refreshTokenExpireDays = new DateInterval($this->config->get('hf_shield.refresh_token_days', 'P1M'));
        $this->implicitGrantEnabled = $this->config->get('hf_shield.implicit_grant_enabled', false);
        $this->defaultScope = $this->config->get('hf_shield.default_scope', 'default');
    }

    /**
     * Invokes the method implementation allowing the creation and configuration
     * of an AuthorizationServer instance with specific grant types and settings.
     *
     * The method sets default configurations for the Authorization Server, enables
     * various grant types such as authorization code, refresh token, password,
     * client credentials, and optionally the implicit grant, depending on the provided
     * configuration.
     *
     * @return AuthorizationServer configured instance of the authorization server
     */
    public function __invoke()
    {
        return tap($this->makeAuthorizationServer(), function (AuthorizationServer $server) {
            $server->setDefaultScope($this->config->get('hf_shield.default_scope', 'default'));
            $server->enableGrantType($this->makeAuthCodeGrant(), $this->tokenExpireDays);
            $server->enableGrantType($this->makeRefreshTokenGrant(), $this->refreshTokenExpireDays);
            $server->enableGrantType($this->makePasswordGrant(), $this->tokenExpireDays);
            $server->enableGrantType(new ClientCredentialsGrant(), $this->tokenExpireDays);
            if ($this->implicitGrantEnabled) {
                $server->enableGrantType($this->makeImplicitGrant(), $this->tokenExpireDays);
            }
            return $server;
        });
    }

    /**
     * Creates and returns an AuthorizationServer instance configured with the necessary repositories,
     * cryptographic key, and encryption key.
     *
     * @return AuthorizationServer the AuthorizationServer instance initialized with specified dependencies and configuration
     */
    public function makeAuthorizationServer(): AuthorizationServer
    {
        return new AuthorizationServer(
            make(ClientRepository::class),
            make(AccessTokenRepository::class),
            make(ScopeRepository::class),
            $this->makeCryptKey(),
            $this->config->get('hf_shield.encryption_key')
        );
    }

    /**
     * Creates an instance of the RefreshTokenGrant, setting up its repository
     * and configuring the Refresh Token Time-To-Live (TTL).
     *
     * @return RefreshTokenGrant the configured RefreshTokenGrant instance
     */
    public function makeRefreshTokenGrant(): RefreshTokenGrant
    {
        $repository = make(RefreshTokenRepository::class);
        $grant = make(RefreshTokenGrant::class, [
            'refreshTokenRepository' => $repository,
        ]);
        $grant->setRefreshTokenTTL($this->tokenExpireDays);
        return $grant;
    }

    /**
     * Creates and configures a PasswordGrant instance with the appropriate user and refresh token repositories.
     * The refresh token time-to-live (TTL) is set based on the predefined token expiration days.
     *
     * @return PasswordGrant the configured PasswordGrant instance
     */
    public function makePasswordGrant(): PasswordGrant
    {
        $grant = make(PasswordGrant::class, [
            'userRepository' => make(UserRepository::class),
            'refreshTokenRepository' => make(RefreshTokenRepository::class),
        ]);
        $grant->setRefreshTokenTTL($this->tokenExpireDays);

        return $grant;
    }

    /**
     * Generates and returns a CryptKey object configured with the private key, passphrase,
     * and permissions check settings.
     *
     * @return CryptKey the CryptKey instance initialized with specific configuration values
     */
    protected function makeCryptKey(): CryptKey
    {
        $key = str_replace('\n', "\n", $this->config->get('hf_shield.private_key'));
        return make(CryptKey::class, [
            'keyPath' => $key,
            'passPhrase' => null,
            'keyPermissionsCheck' => false,
        ]);
    }

    /**
     * Creates and returns an AuthCodeGrant instance configured with the required repositories and settings.
     *
     * @return AuthCodeGrant the AuthCodeGrant instance initialized with the specified repositories and configurations
     */
    protected function makeAuthCodeGrant(): AuthCodeGrant
    {
        return make(
            AuthCodeGrant::class,
            [
                'authCodeRepository' => make(AuthCodeRepository::class),
                'refreshTokenRepository' => make(RefreshTokenRepository::class),
                'authCodeTTL' => $this->tokenExpireDays,
            ]
        );
    }

    /**
     * Creates and returns an instance of the ImplicitGrant class configured with
     * the access token time-to-live (TTL) setting.
     *
     * @return ImplicitGrant the ImplicitGrant instance initialized with the specified TTL
     */
    protected function makeImplicitGrant(): ImplicitGrant
    {
        return make(ImplicitGrant::class, [
            'accessTokenTTL' => $this->tokenExpireDays,
        ]);
    }
}
