<?php

namespace Infrastructure\Symfony\Controller;

use Domain\Request\LoginRequest;
use Domain\Service\Auth\AuthServiceInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;
use Symfony\Component\Serializer\SerializerInterface;

class LoginCreds {
    public ?string $email;
    public ?string $password;
}

class AuthController extends AbstractController
{
    #[Route('/api/token', name: 'api.login', methods: ['POST'])]
    public function login(
        Request $request, 
        LoginRequest $loginRequest, 
        SerializerInterface $serializer, 
        AuthServiceInterface $authService): Response
    {
        $deserializedCreds = $serializer->deserialize($request->getContent(), LoginCreds::class, 'json');

        $loginRequest->setLoginCredentials($deserializedCreds->email, $deserializedCreds->password);

        $loginRequest->setClientIp($request->getClientIp());

        $response = $authService->login($loginRequest);

        $serializedResponse = $serializer->serialize($response, 'json', [AbstractObjectNormalizer::SKIP_NULL_VALUES => true]);

        return new Response($serializedResponse, $response->statusCode, ['Content-Type' => "text/json"]);
    }

    #[Route("/api/refresh-token/{refreshToken}/token", methods: ['POST'])]
    public function refreshToken(
        string $refreshToken,
        AuthServiceInterface $authService,
        SerializerInterface $serializer
    ) {
        $response = $authService->refreshToken($refreshToken);

        $serializedResponse = $serializer->serialize($response, 'json', [AbstractObjectNormalizer::SKIP_NULL_VALUES => true]);

        return new Response($serializedResponse, $response->statusCode, ['Content-Type' => "text/json"]);
    }

    #[Route('/api/validate/{accessToken}', name: 'api.token.validate', methods: ['GET'])]
    public function validateToken(
        string $accessToken,
        AuthServiceInterface $authService,
        SerializerInterface $serializer
    ) {
        $response = $authService->validateToken($accessToken);

        $serializedResponse = $serializer->serialize($response, 'json', [AbstractObjectNormalizer::SKIP_NULL_VALUES => true]);

        return new Response($serializedResponse, $response->statusCode, ['Content-Type' => "text/json"]);
    }
}