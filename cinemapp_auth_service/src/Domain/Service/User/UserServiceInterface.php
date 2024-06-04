<?php

namespace Domain\Service\User;

use Domain\Interface\UserDto\Input\CreateUserInput;
use Domain\Interface\UserDto\Input\UpdateUserInput;
use Domain\Request\AddNewUserRequest;
use Domain\Request\UpdateUserRequest;
use Domain\Response\User\AddNewUserResponse;
use Domain\Response\User\GetUserByIdResponse;
use Domain\Response\User\UpdateUserResponse;

interface UserServiceInterface
{
    public function addNewUser(CreateUserInput $newUser): AddNewUserResponse;
    public function getUserById(string $userUuid): GetUserByIdResponse;
    public function updateUser(UpdateUserInput $updateUserInput): UpdateUserResponse;
}