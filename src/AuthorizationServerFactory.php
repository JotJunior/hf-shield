<?php

declare(strict_types=1);

namespace Jot\HfOAuth2;

use Hyperf\Di\Annotation\Inject;
use Hyperf\Contract\ConfigInterface;
use Jot\HfOAuth2\Repository\AccessTokenRepository;
use Jot\HfOAuth2\Repository\AuthCodeRepository;
use Jot\HfOAuth2\Repository\ClientRepository;
use Jot\HfOAuth2\Repository\RefreshTokenRepository;
use Jot\HfOAuth2\Repository\ScopeRepository;
use Jot\HfOAuth2\Repository\UserRepository;
use Psr\Container\ContainerInterface;
use League\OAuth2\Server\AuthorizationServer;
use League\OAuth2\Server\Grant\AuthCodeGrant;
use League\OAuth2\Server\Grant\PasswordGrant;
use League\OAuth2\Server\Grant\RefreshTokenGrant;
use League\OAuth2\Server\Grant\ImplicitGrant;
use League\OAuth2\Server\CryptKey;
use League\OAuth2\Server\Grant\ClientCredentialsGrant;

use function Hyperf\Support\make;
use function Hyperf\Tappable\tap;

class AuthorizationServerFactory
{

    #[Inject]
    protected ContainerInterface $container;

    #[Inject]
    protected ConfigInterface $config;

    protected \DateInterval|null $tokenExpireDays = null;
    protected \DateInterval|null $refreshTokenExpireDays = null;
    protected bool $implicitGrantEnabled = false;
    protected string $defaultScope = 'default';


    /**
     * Constructor method to initialize the class with dependency injection and configuration settings.
     *
     * @param ContainerInterface $container The service container instance.
     * @param ConfigInterface $config The configuration instance providing necessary settings.
     *
     * @return void
     * @throws \DateMalformedIntervalStringException
     */
    public function __construct(ContainerInterface $container, ConfigInterface $config)
    {
        $this->container = $container;
        $this->config = $config;
        $this->tokenExpireDays = new \DateInterval($this->config->get('hf_oauth2.token_days', 'P1D'));
        $this->refreshTokenExpireDays = new \DateInterval($this->config->get('hf_oauth2.refresh_token_days', 'P1M'));
        $this->implicitGrantEnabled = $this->config->get('hf_oauth2.implicit_grant_enabled', false);
        $this->defaultScope = $this->config->get('hf_oauth2.default_scope', 'default');
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
     * @return AuthorizationServer Configured instance of the authorization server.
     */
    public function __invoke()
    {
        return tap($this->makeAuthorizationServer(), function (AuthorizationServer $server) {
            $server->setDefaultScope($this->config->get('hf_oauth2.default_scope', 'default'));
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
     * Creates and returns an AuthCodeGrant instance configured with the required repositories and settings.
     *
     * @return AuthCodeGrant The AuthCodeGrant instance initialized with the specified repositories and configurations.
     */
    protected function makeAuthCodeGrant(): AuthCodeGrant
    {
        return make(AuthCodeGrant::class, [
                'authCodeRepository' => make(AuthCodeRepository::class),
                'refreshTokenRepository' => make(RefreshTokenRepository::class),
                'authCodeTTL' => $this->tokenExpireDays,
            ]
        );
    }


    /**
     * Creates an instance of the RefreshTokenGrant, setting up its repository
     * and configuring the Refresh Token Time-To-Live (TTL).
     *
     * @return RefreshTokenGrant The configured RefreshTokenGrant instance.
     */
    public function makeRefreshTokenGrant()
    {
        $repository = make(RefreshTokenRepository::class);
        $grant = make(RefreshTokenGrant::class, [
            'refreshTokenRepository' => $repository
        ]);
        $grant->setRefreshTokenTTL($this->tokenExpireDays);
        return $grant;
    }


    /**
     * Creates and configures a PasswordGrant instance with the appropriate user and refresh token repositories.
     * The refresh token time-to-live (TTL) is set based on the predefined token expiration days.
     *
     * @return PasswordGrant The configured PasswordGrant instance.
     */
    public function makePasswordGrant()
    {
        $grant = make(PasswordGrant::class, [
            'userRepository' => make(UserRepository::class),
            'refreshTokenRepository' => make(RefreshTokenRepository::class)
        ]);
        $grant->setRefreshTokenTTL($this->tokenExpireDays);

        return $grant;
    }


    /**
     * Creates and returns an instance of the ImplicitGrant class configured with
     * the access token time-to-live (TTL) setting.
     *
     * @return ImplicitGrant The ImplicitGrant instance initialized with the specified TTL.
     */
    protected function makeImplicitGrant()
    {
        return make(ImplicitGrant::class, [
            'accessTokenTTL' => $this->tokenExpireDays
        ]);
    }


    /**
     * Creates and returns an AuthorizationServer instance configured with the necessary repositories,
     * cryptographic key, and encryption key.
     *
     * @return AuthorizationServer The AuthorizationServer instance initialized with specified dependencies and configuration.
     */
    public function makeAuthorizationServer()
    {
        return new AuthorizationServer(
            make(ClientRepository::class),
            make(AccessTokenRepository::class),
            make(ScopeRepository::class),
            $this->makeCryptKey(),
            $this->config->get('hf_oauth2.encryption_key')
        );
    }

    /**
     * Generates and returns a CryptKey object configured with the private key, passphrase,
     * and permissions check settings.
     *
     * @return CryptKey The CryptKey instance initialized with specific configuration values.
     */
    protected function makeCryptKey(): CryptKey
    {
        $key = str_replace('\\n', "\n", $this->config->get('hf_oauth2.private_key'));
        return make(CryptKey::class, [
            'keyPath' => $key,
            'passPhrase' => null,
            'keyPermissionsCheck' => false
        ]);
    }

}