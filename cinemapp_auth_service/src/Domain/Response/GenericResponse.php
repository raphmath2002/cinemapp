<?php

namespace Domain\Response;

class GenericResponse
{
    public const HTTP_OK            = 200;
    public const HTTP_CREATED       = 201;
    public const HTTP_NO_CONTENT    = 204;
    public const HTTP_NOT_FOUND     = 404;
    public const HTTP_UNAUTHORIZED  = 401;
    public const HTTP_FORBIDDEN     = 403;
    public const HTTP_EXCEPTION     = 500;
    public const HTTP_UNPROCESSABLE = 422;
    public const HTTP_LOCKED        = 423;

    public ?int $statusCode = null;

    public ?string $message = null;

    public ?string $exceptionMessage = null;

    public $data = null;

    public $fieldErrors = null;

    protected function hasFieldErrors(): bool
    {
        return count($this->fieldErrors) > 0;
    }

    public function setMessage(string $message)
    {
        $this->message = $message;
    }

    private function setStatus(int $code)
    {
        $this->statusCode = $code;
    }

    public function addFieldErrorMessage(string $field, string $message) {
        is_null($this->fieldErrors) && $this->fieldErrors = [];
        isset($this->fieldErrors[$field]) 
            ? $this->fieldErrors[$field][] = $message
            : $this->fieldErrors[$field] = [$message];
    }

    public function setValidationError()
    {
        $this->setMessage("Some fields has validation errors");
        $this->setStatus(self::HTTP_UNPROCESSABLE);
    }

    public function setException(string $message)
    {
        $this->statusCode = self::HTTP_EXCEPTION;
        $this->exceptionMessage = 'An error occured';
        $this->message = $message;
    }

    public function notFound()
    {
        $this->message = "The resource you try to get do not exists";
        $this->statusCode = self::HTTP_NOT_FOUND;
    }

    public function setData($data) 
    {
        $this->data = $data;
    }

    public function fetchOk()
    {
        $this->statusCode = self::HTTP_OK;
    }
}