<?php

namespace Jot\HfOAuth2\Controller;

use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\DeleteMapping;
use Hyperf\HttpServer\Annotation\PostMapping;
use Hyperf\HttpServer\Annotation\PutMapping;
use Hyperf\RateLimit\Annotation\RateLimit;
use Hyperf\Stringable\Str;
use Jot\HfOAuth2\Entity\UserEntity;
use Jot\HfOAuth2\Repository\UserRepository;
use Jot\HfRepository\Exception\EntityValidationWithErrorsException;
use Psr\Http\Message\ResponseInterface as PsrResponseInterface;
use function Hyperf\Support\make;

#[Controller(prefix: '/oauth')]
class UserController extends AbstractController
{

    protected string $repository = UserRepository::class;

    /**
     * Creates a new user by processing provided user data, generating a password salt,
     * and validating the user entity. If validation fails, it returns a response with errors.
     *
     * @return PsrResponseInterface Returns a response containing the saved user data or validation errors.
     */
    #[PostMapping(path: 'users')]
    #[RateLimit(create: 1, capacity: 2)]
    public function createUser(): PsrResponseInterface
    {
        $userData = $this->request->all();
        $userData['password_salt'] = Str::uuid()->toString();;
        $user = make(UserEntity::class, ['data' => $userData]);

        return $this->saveUser($user);
    }

    #[PutMapping(path: 'users/{id}')]
    #[RateLimit(create: 1, capacity: 2)]
    public function updateUser(string $id): PsrResponseInterface
    {
        $userData = $this->request->all();
        $userData['id'] = $id;
        $user = make(UserEntity::class, ['data' => $userData]);
        $user->setEntityState('update');
        if (!$user->validate()) {
            return $this->response->withStatus(400)->json($user->getErrors());
        }
        $updatedUser = $this->repository()->update($user);
        return $this->response->json($updatedUser->toArray());
    }

    /**
     * Saves a user entity to the repository and returns a JSON response.
     *
     * @param UserEntity $user The user entity to be saved.
     * @return PsrResponseInterface Returns a JSON response containing the created user data or an error message on failure.
     * @throws EntityValidationWithErrorsException
     */
    #[DeleteMapping(path: 'users/{id}')]
    #[RateLimit(create: 1, capacity: 2)]
    private function saveUser(UserEntity $user): PsrResponseInterface
    {
        $createdUser = $this->repository()->createUser($user);
        $userData = $createdUser
            ->hide(['password_salt', 'password'])
            ->toArray();
        return $this->response->json($userData);
    }

}