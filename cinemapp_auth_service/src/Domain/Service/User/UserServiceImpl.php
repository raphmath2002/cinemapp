<?php

namespace Domain\Service\User;

use Domain\Entity\User;
use Domain\Interface\UserDto\Input\CreateUserInput;
use Domain\Interface\UserDto\Input\UpdateUserInput;
use Domain\Interface\UserDto\UserOutput;
use Domain\Request\UpdateUserRequest;
use Domain\Response\User\AddNewUserResponse;
use Domain\Response\User\GetUserByIdResponse;
use Domain\Response\User\UpdateUserResponse;
use Infrastructure\Helper\ObjectHydrator;
use Infrastructure\Symfony\Repository\User\UserRepositoryInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class UserServiceImpl implements UserServiceInterface
{

    public function __construct(
        protected ValidatorInterface $validator,
        protected UserRepositoryInterface $userRepository
    ) {
    }

    public function addNewUser(CreateUserInput $newUserDTO): AddNewUserResponse
    {
        $response = new AddNewUserResponse();

        $errors = $this->validator->validate($newUserDTO);

        if (count($errors) > 0) {
            $response->setValidationError();

            foreach ($errors as $error) {
                $response->addFieldErrorMessage(
                    $error->getPropertyPath(),
                    $error->getMessage()
                );
            }
            return $response;
        }

        $newUser = $newUserDTO->transform();

        $newUser->uuid = $this->generateUUIDv4();

        try {

            $hashedPass = password_hash($newUser->password, PASSWORD_BCRYPT, ['cost' => 10]);

            $newUser->password = $hashedPass;

            $newUser = $this->userRepository->storeUser($newUser);
        } catch (\Exception $e) {
            $response->setException($e);
            return $response;
        }

        $newUserOutput = UserOutput::create($newUser)->toArray();

        $response->setData($newUserOutput);
        $response->userCreated();
        return $response;
    }

    public function getUserById(string $userUuid): GetUserByIdResponse
    {
        $response = new GetUserByIdResponse();

        try {
            $user = $this->userRepository->getUserById($userUuid);

            if (is_null($user)) {
                $response->notFound();
                return $response;
            }

            $response->fetchOk();
            $response->setData($user);
        } catch (\Exception $e) {
            $response->setException($e);
            return $response;
        }

        return $response;
    }

    public function updateUser(UpdateUserInput $updateUserInput): UpdateUserResponse
    {
        $response = new UpdateUserResponse();

        $user = $this->userRepository->getUserById($updateUserInput->uuid);

        if (is_null($user)) {
            $response->notFound();
            return $response;
        }

        $errors = $this->validator->validate($updateUserInput);

        if (count($errors) > 0) {
            $response->setValidationError();

            foreach ($errors as $error) {
                $response->addFieldErrorMessage(
                    $error->getPropertyPath(),
                    $error->getMessage()
                );
            }
            return $response;
        }

        $updatedData = $updateUserInput->toArray();

        !is_null($updatedData['first_name']) && $user->first_name = $updatedData['first_name'];
        !is_null($updatedData['last_name']) && $user->last_name = $updatedData['last_name'];
        !is_null($updatedData['email']) && $user->email = $updatedData['email'];
        !is_null($updatedData['roles']) && $user->setRoles($updatedData['roles']);
        !is_null($updatedData['status']) && $user->status = $updatedData['status'];

        try {
            $this->userRepository->updateUser($user);
            $response->userUpdated();
        } catch (\Exception $e) {
            $response->setException($e);
            return $response;
        }

        return $response;
    }

    private function generateUUIDv4()
    {
        $data = random_bytes(16);
        assert(strlen($data) == 16);

        // Set version to 0100
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
        // Set bits 6-7 to 10
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80);

        // Output the 36 character UUID.
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }
}
