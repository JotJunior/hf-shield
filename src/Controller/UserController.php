<?php

namespace Jot\HfOAuth2\Controller;

use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\DeleteMapping;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Annotation\PostMapping;
use Hyperf\HttpServer\Annotation\PutMapping;
use Hyperf\RateLimit\Annotation\RateLimit;
use Jot\HfOAuth2\Annotation\Scope;
use Jot\HfOAuth2\Entity\User\User;
use Jot\HfOAuth2\Middleware\CheckCredentials;
use Jot\HfOAuth2\Repository\UserRepository;
use Psr\Http\Message\ResponseInterface as PsrResponseInterface;
use function Hyperf\Support\make;

#[Controller(prefix: '/oauth')]
class UserController extends AbstractController
{

    protected string $repository = UserRepository::class;

    #[PostMapping(path: 'users')]
    #[Scope(allow: 'oauth:user:create')]
    #[RateLimit(create: 1, capacity: 2)]
    #[Middleware(CheckCredentials::class)]
    public function createUser(): PsrResponseInterface
    {
        $userData = $this->request->all();
        $user = make(User::class, ['data' => $userData]);

        return $this->saveUser($user);
    }

    #[DeleteMapping(path: 'users/{id}')]
    #[RateLimit(create: 1, capacity: 2)]
    #[Scope(allow: 'oauth:user:delete')]
    private function saveUser(User $user): PsrResponseInterface
    {
        $createdUser = $this->repository()->create($user);
        $userData = $createdUser
            ->hide(['password_salt', 'password'])
            ->toArray();
        return $this->response->json($userData);
    }

    #[PutMapping(path: 'users/{id}')]
    #[Scope(allow: 'oauth:user:update')]
    #[RateLimit(create: 1, capacity: 2)]
    #[Middleware(CheckCredentials::class)]
    public function updateUser(string $id): PsrResponseInterface
    {
        $userData = $this->request->all();
        $userData['id'] = $id;
        $user = make(User::class, ['data' => $userData]);
        $user->setEntityState('update');
        if (!$user->validate()) {
            return $this->response->withStatus(400)->json($user->getErrors());
        }
        $updatedUser = $this->repository()->update($user);
        return $this->response->json($updatedUser->toArray());
    }

}