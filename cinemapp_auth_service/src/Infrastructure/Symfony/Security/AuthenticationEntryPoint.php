<?php

namespace Infrastructure\Symfony\Security;

use Domain\Response\Auth\LoginResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\EntryPoint\AuthenticationEntryPointInterface;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;
use Symfony\Component\Serializer\SerializerInterface;

class AuthenticationEntryPoint implements AuthenticationEntryPointInterface
{

    public function __construct(
        protected SerializerInterface $serializer
    ) {}

    public function start(Request $request, ?AuthenticationException $authException = null): Response
    {
        $response = new LoginResponse();
        $response->nonLoggedUser();

        $serializedResponse = $this->serializer->serialize($response, 'json', [AbstractObjectNormalizer::SKIP_NULL_VALUES => true]);

        return new Response($serializedResponse, $response->statusCode, ['Content-Type' => "text/json"]);
    }
}