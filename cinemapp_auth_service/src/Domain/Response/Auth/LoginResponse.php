<?php

namespace Domain\Response\Auth;

use Domain\Response\GenericResponse;

class LoginResponse extends GenericResponse
{
    public function userLogged()
    {
        $this->statusCode = parent::HTTP_OK;
    }

    public function userNotFound()
    {
        $this->setMessage('User not found');
        $this->statusCode = parent::HTTP_NOT_FOUND;
    }

    public function loginError()
    {
        $this->setMessage('Invalid credentials');
        $this->statusCode = parent::HTTP_UNAUTHORIZED;
    }

    public function userNotGranted()
    {
        $this->setMessage('You are not allowed to access to this resource');
        $this->statusCode = parent::HTTP_FORBIDDEN;
    }

    public function userLocked()
    {
        $this->setMessage("Your account has been locked, please retry later");
        $this->statusCode = parent::HTTP_LOCKED;
    }

    public function nonLoggedUser()
    {
        $this->setMessage("You are not authorized to access to this resource");
        $this->statusCode = parent::HTTP_UNAUTHORIZED;

    }

    public function logged($access, $refresh)
    {
        $this->statusCode = parent::HTTP_OK;
        $this->setData([
            'auth' => [
                "accessToken" => $access->token,
                "accessTokenExpireAt" => $access->expire_at,
                "refreshToken" => $refresh->token,
                "refreshTokenExpireAt" => $refresh->expire_at,
            ]
        ]);
    }
}