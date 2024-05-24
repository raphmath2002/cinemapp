<?php

namespace Domain\Service\Auth;

use Domain\Request\LoginRequest;
use Domain\Response\Auth\LoginResponse;
use Domain\Response\GenericResponse;

interface AuthServiceInterface
{
    public function login(LoginRequest $request): LoginResponse;
    public function refreshToken(string $refreshToken): LoginResponse;
    public function validateToken(string $token): GenericResponse;
}