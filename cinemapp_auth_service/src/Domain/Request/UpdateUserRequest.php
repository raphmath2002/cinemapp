<?php

namespace Domain\Request;

class UpdateUserRequest
{
    protected $userId = null;
    protected $updatedData = null;

    public function setData($id, $data)
    {
        $this->userId = $id;

        if(array_key_exists('roles', $data) && gettype($data['roles']) != "array") {
            unset($data['roles']);
        }

        $this->updatedData = $data;
    }

    public function getData()
    {
        return [
            "id" => $this->userId,
            "data" => $this->updatedData
        ];
    }
    
}