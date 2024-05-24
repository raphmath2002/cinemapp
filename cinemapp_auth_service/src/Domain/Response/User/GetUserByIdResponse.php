<?php

namespace Domain\Response\User;

use Domain\Response\GenericResponse;

class GetUserByIdResponse extends GenericResponse 
{
    public function setData($user)
    {
        parent::setData([
            'user' => [
                "first_name" => $user->first_name,
                "last_name" => $user->last_name,
                "email" => $user->email,
                "roles" => $user->getRoles(),
                "status" => $user->status
            ]
        ]);
    }

}