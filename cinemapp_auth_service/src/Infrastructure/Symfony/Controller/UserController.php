<?php

namespace Infrastructure\Symfony\Controller;

use Domain\Interface\UserDto\Input\CreateUserInput;
use Domain\Interface\UserDto\Input\UpdateUserInput;
use Domain\Request\AddNewUserRequest;
use Domain\Request\UpdateUserRequest;
use Domain\Response\GenericResponse;
use Domain\Service\User\UserServiceInterface;
use Infrastructure\Helper\ObjectHydrator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;
use Symfony\Component\Serializer\SerializerInterface;

class UserController extends AbstractController
{

    private function getTokenPayload(string $authorizationHeader): array
    {

        $token = trim(str_replace("Bearer", "", $authorizationHeader));

        [$_, $payload, $__] = explode(".", $token);

        $decodedPayload = json_decode(base64_decode($payload), true);

        return $decodedPayload;
    }

    #[Route("/api/account", name: 'api.account.store', methods: ['POST'], format: 'json')]
    public function store(
        Request $request,
        UserServiceInterface $userService,
        SerializerInterface $serializer
    ): Response {

        $createUserInput = ObjectHydrator::hydrate(
            json_decode($request->getContent(), true),
            new CreateUserInput(),
            false
        );

        $response = $userService->addNewUser($createUserInput);

        $serializedResponse = $serializer->serialize($response, 'json', [AbstractObjectNormalizer::SKIP_NULL_VALUES => true]);

        return new Response($serializedResponse, $response->statusCode, ['Content-Type' => "text/json"]);
    }

    #[Route("/api/account/{uuid}", name: 'api.account.view', methods: ['GET'], format: 'json')]
    public function view(
        string $uuid,
        Request $request,
        SerializerInterface $serializer,
        UserServiceInterface $userService
    ) {

        $authorizationHeader = $request->headers->get('Authorization');
        $payload = $this->getTokenPayload($authorizationHeader);

        $response = null;

        if ($uuid === "me" || $uuid === $payload['user_id']) {
            $response = $userService->getUserById($payload['user_id']);
        } else {
            $this->denyAccessUnlessGranted("ROLE_ADMIN");
            $response = $userService->getUserById($uuid);
        }

        $serializedResponse = $serializer->serialize($response, 'json', [AbstractObjectNormalizer::SKIP_NULL_VALUES => true]);
        return new Response($serializedResponse, $response->statusCode, ['Content-Type' => "text/json"]);
    }

    #[Route("/api/account/{uuid}", name: 'api.account.update', methods: ['PUT'])]
    public function update(
        string $uuid,
        Request $request,
        SerializerInterface $serializer,
        UpdateUserRequest $updateUserRequest,
        UserServiceInterface $userService
    ) {
        $data = json_decode($request->getContent(), true);

        $authorizationHeader = $request->headers->get('Authorization');
        $payload = $this->getTokenPayload($authorizationHeader);

        if(!in_array("ROLE_ADMIN", $payload['user_roles']) && isset($data['roles'])) {
            unset($data['roles']);
        }   

        $updateUserInput = ObjectHydrator::hydrate(
            $data,
            new UpdateUserInput(),
            false
        );

        if ($uuid === "me" || $payload['user_id'] === $uuid) {
            $updateUserInput->uuid = $payload['user_id'];
        } else {
            $this->denyAccessUnlessGranted("ROLE_ADMIN");
            $updateUserInput->uuid = $uuid;
        }

        $response = $userService->updateUser($updateUserInput);

        $serializedResponse = $serializer->serialize($response, 'json', [AbstractObjectNormalizer::SKIP_NULL_VALUES => true]);
        return new Response($serializedResponse, $response->statusCode, ['Content-Type' => "text/json"]);
    }
}
