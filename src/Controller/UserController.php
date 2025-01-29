<?php

namespace Jot\HfOAuth2\Controller;

use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\Stringable\Str;
use Jot\HfOAuth2\Entity\UserEntity;
use Jot\HfOAuth2\Repository\UserRepository;
use Jot\HfRepository\Exception\EntityValidationWithErrorsException;
use Psr\Http\Message\ResponseInterface as PsrResponseInterface;
use function Hyperf\Support\make;

#[Controller]
class UserController extends AbstractController
{

    protected string $repository = UserRepository::class;

    /**
     * Creates a new user by processing provided user data, generating a password salt,
     * and validating the user entity. If validation fails, it returns a response with errors.
     *
     * @return PsrResponseInterface Returns a response containing the saved user data or validation errors.
     */
    public function createUser(): PsrResponseInterface
    {
        $userData = $this->request->all();
        $userData['password_salt'] = Str::uuid()->toString();;
        $user = make(UserEntity::class, ['data' => $userData]);

        return $this->saveUser($user);
    }

    public function updateUser(string $id): PsrResponseInterface
    {
        try {
            $userData = $this->request->all();
            $userData['id'] = $id;
            $user = make(UserEntity::class, ['data' => $userData]);
            $user->setEntityState('update');
            if (!$user->validate()) {
                return $this->response->withStatus(400)->json($user->getErrors());
            }
            $updatedUser = $this->repository()->update($user);
            return $this->response->json($updatedUser->toArray());
        } catch (\Throwable $e) {
            $errorResponse = [
                'error' => $e->getMessage(),
                'messages' => method_exists($e, 'getErrors') ? $e->getErrors() : [],
                'trace' => $e->getTrace()
            ];
            return $this->response->withStatus(500)->json($errorResponse);
        }

    }

    /**
     * Saves a user entity to the repository and returns a JSON response.
     *
     * @param UserEntity $user The user entity to be saved.
     * @return PsrResponseInterface Returns a JSON response containing the created user data or an error message on failure.
     */
    private function saveUser(UserEntity $user): PsrResponseInterface
    {
        try {
            if (!$user->validate()) {
                throw new EntityValidationWithErrorsException($user->getErrors());
            }
            $user->createHash($user->getPassword(), $user->getPasswordSalt());

            $createdUser = $this->repository()->create($user);
            $userData = $createdUser
                ->hide(['password_salt', 'password'])
                ->toArray();
            return $this->response->json($userData);
        } catch (\Throwable $e) {
            $errorResponse = [
                'error' => $e->getMessage(),
                'messages' => method_exists($e, 'getErrors') ? $e->getErrors() : []
            ];
            return $this->response->withStatus(500)->json($errorResponse);
        }
    }

}