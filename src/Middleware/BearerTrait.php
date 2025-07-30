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
use Hyperf\Stringable\Str;
use Jot\HfShield\AllowedScopes;
use Jot\HfShield\Exception\MissingResourceScopeException;
use Jot\HfShield\Exception\UnauthorizedAccessException;
use Jot\HfShield\Exception\UnauthorizedClientException;
use Jot\HfShield\Exception\UnauthorizedScopeException;
use Jot\HfShield\Exception\UnauthorizedUserException;
use Jot\HfShield\LoggerContextCollector;
use League\OAuth2\Server\Exception\OAuthServerException;
use Psr\Http\Message\ServerRequestInterface;

use function Hyperf\Translation\__;

trait BearerTrait
{
    use LoggerContextCollector;

    public const ATTR_ACCESS_TOKEN_ID = 'oauth_access_token_id';

    public const ATTR_CLIENT_ID = 'oauth_client_id';

    public const ATTR_USER_ID = 'oauth_user_id';

    public const ATTR_SCOPES = 'oauth_scopes';

    protected ?string $oauthTokenId = null;

    /**
     * Generates a descriptive message based on the provided content and the current resource scope.
     *
     * This method constructs a message that describes the user's action, such as creating or performing
     * other operations on a resource. The generated message dynamically incorporates the user's name,
     * the action performed, the type of resource, and the resource's name, depending on the context.
     *
     * @param array $content An associative array contain
     *                       - 'user' => ['name'] (string): The name of the user ping details about the user, resource, and context.
     *                       Expected keys include:erforming the action.
     *                       - 'context' => ['request' => ['body' => ['name']]] (string): The name of the resource.
     * @return string a string message describing the user action on the resource
     */
    public function generateMessage(array $content): string
    {
        $scope = current($this->resourceScopes);
        if (empty($scope)) {
            return '';
        }

        $scopeData = $this->repository->fetchEntityReference(index: 'scopes', id: $scope, fields: ['id', 'name', 'domain', 'domain_name', 'resource', 'resource_name', 'action']);

        $parts = explode(':', $scope);

        $resource = Str::singular($parts[1]);
        $resources = Str::plural($parts[1]);
        $action = $parts[2] ?? '';

        // Recupera os valores do array com valores padrão
        $user = $content['user']['name'] ?? 'hf-shield.session_actions.undefined_user';
        $action = $action ? __(sprintf('hf-shield.session_actions.%s', $action)) : '';
        $resourceType = $scopeData['resource_name'] ?? $resource ? __(sprintf('messages.scopes.%s', $resource)) : '';
        $pluralResourceType = __(sprintf('messages.scopes.%s', $resources));
        $resourceName = $content['request']['body']['name'] ?? '';

        switch ($action) {
            case 'create':
                $message = __('hf-shield.log_messages.user_create_new', [
                    'resource' => $scopeData['domain_name'] ?? $resourceType,
                    'name' => $scopeData['resource_name'] ?? $resourceName,
                ]);
                break;
            case 'list':
            case 'pairs':
                $message = __('hf-shield.log_messages.user_list_resources', [
                    'resources' => $scopeData['resource_name'] ?? $pluralResourceType,
                ]);
                break;
            case 'session':
                $message = __('hf-shield.log_messages.system_view_user', ['user' => $user]);
                break;
            case 'update':
            case 'delete':
                $message = __('hf-shield.log_messages.user_action_resource_name', [
                    'action' => $action,
                    'resource' => $scopeData['domain_name'] ?? $resourceType,
                    'name' => $scopeData['resource_name'] ?? $resourceName,
                ]);
                break;
            default:
                $message = __('hf-shield.log_messages.user_action_resource', [
                    'action' => $action,
                    'resource' => $scopeData['resource_name'] ?? $resourceType,
                ]);
                break;
        }

        return $message;
    }

    public function getOauthClient(): array
    {
        $client = $this->repository->isClientValid(
            $this->request->getAttribute(self::ATTR_CLIENT_ID)
        );
        if (! $client) {
            throw new UnauthorizedClientException($this->metadata());
        }
        return $client;
    }

    public function getOauthUser(): array
    {
        $userId = $this->request->getAttribute(self::ATTR_USER_ID);
        if (empty($userId)) {
            throw new UnauthorizedUserException([]);
        }
        return $this->repository->getUserSessionData($userId);
    }

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
            echo $e->getMessage(), PHP_EOL;
            throw new UnauthorizedAccessException($this->metadata());
        }

        $this->collectResourceScopes();
        if (empty($this->resourceScopes)) {
            $this->logger->error('Missing resource scope', $this->metadata());
            throw new MissingResourceScopeException();
        }
        $this->validateRequestAttributes();
    }

    /**
     * Collects and structures metadata including user details, server parameters, and request data.
     *
     * This method organizes metadata into a structured array containing information about the user,
     * server parameters, and request specifics. It also generates an additional message based on the
     * collected metadata.
     *
     * @return array an associative array containing organized metadata
     */
    protected function metadata(): array
    {
        $this->oauthTokenId = $this->request->getAttribute(self::ATTR_ACCESS_TOKEN_ID);
        $metadata = $this->collectMetadata();
        $metadata['message'] = $this->generateMessage($metadata);

        return $metadata;
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
            throw new UnauthorizedScopeException($this->metadata());
        }

        $client = $this->getOauthClient();

        $userId = $this->request->getAttribute(self::ATTR_USER_ID);

        if (! $this->repository->isUserValid($userId, $client['tenant']['id'], $this->resourceScopes)) {
            throw new UnauthorizedUserException($this->metadata());
        }

        $this->request->withAttribute('oauth_user_session', $this->getOauthUser());
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
            throw new $exceptionClass($this->metadata());
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

    protected function logRequest(): void
    {
        $metadata = $this->metadata();
        $this->log($metadata['message']);
    }
}
