<?php

namespace Domain\Response\User;

use Domain\Response\GenericResponse;

class UpdateUserResponse extends GenericResponse 
{
    public function userUpdated()
    {
        $this->statusCode = parent::HTTP_NO_CONTENT;
    }
}