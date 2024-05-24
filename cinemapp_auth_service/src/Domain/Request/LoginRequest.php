<?php

namespace Domain\Request;

class LoginRequest
{
    protected ?string $email = null;
    protected ?string $password = null;
    protected ?string $clientIp = null;

    public function setLoginCredentials(string $email, string $password) {
        $this->email = $email;
        $this->password = $password;
    }

    public function setClientIp(?string $clientIp)
    {
        $this->clientIp = $clientIp;
    }

    public function getClientIp(): ?string
    {
        return $this->clientIp;
    }

    public function getLoginCredentials()
    {
        return (object) [
            'email' => $this->email,
            'password' => $this->password
        ];
    }
}