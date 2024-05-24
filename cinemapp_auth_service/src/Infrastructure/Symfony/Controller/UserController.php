<?php

namespace Infrastructure\Symfony\Controller;

use Domain\Interface\UserDto\Input\CreateUserInput;
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

    private function getTokenPayload(string $token): array
    {
        [$header, $payload, $signatureFromUser] = explode(".", $token);

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
            new CreateUserInput()
        );

        $response = $userService->addNewUser($createUserInput);

        $serializedResponse = $serializer->serialize($response, 'json', [AbstractObjectNormalizer::SKIP_NULL_VALUES => true]);

        return new Response($serializedResponse, $response->statusCode, ['Content-Type' => "text/json"]);
    }

    #[Route("/api/account/{id}", name: 'api.account.view', methods: ['GET'], format: 'json')]
    public function view(
        $id,
        Request $request,
        SerializerInterface $serializer,
        UserServiceInterface $userService
    ) {

        $authorizationHeader = $request->headers->get('Authorization');
        $token = trim(str_replace("Bearer", "", $authorizationHeader));
        $payload = $this->getTokenPayload($token);

        $response = null;

        if ($id === "me" || (intval($id) && (int) $id === $payload['user_id'])) {
            $response = $userService->getUserById($payload['user_id']);
        } else if (intval($id)) {
            $this->denyAccessUnlessGranted("ROLE_ADMIN");
            $response = $userService->getUserById((int) $id);
        } else {
            $response = new GenericResponse();
            $response->setMessage("Please provide a valid id parameter");
            $response->statusCode = 422;
        }

        $serializedResponse = $serializer->serialize($response, 'json', [AbstractObjectNormalizer::SKIP_NULL_VALUES => true]);
        return new Response($serializedResponse, $response->statusCode, ['Content-Type' => "text/json"]);
    }

    #[Route("/api/account/{id}", name: 'api.account.update', methods: ['PUT'])]
    public function update(
        $id,
        Request $request,
        SerializerInterface $serializer,
        UpdateUserRequest $updateUserRequest,
        UserServiceInterface $userService
    ) {
        $data = json_decode($request->getContent(), true);

        $authorizationHeader = $request->headers->get('Authorization');
        $token = trim(str_replace("Bearer", "", $authorizationHeader));
        $payload = $this->getTokenPayload($token);

        if(!in_array("ROLE_ADMIN", $payload['user_roles']) && isset($data['roles'])) {
            unset($data['roles']);
        }   

        if ($id === "me" || (intval($id) && (int) $id === $payload['user_id'])) {
            $updateUserRequest->setData($payload['user_id'], $data);
            $response = $userService->updateUser($updateUserRequest);
        } else if (intval($id)) {
            $this->denyAccessUnlessGranted("ROLE_ADMIN");
            $updateUserRequest->setData((int) $id, $data);
            $response = $userService->updateUser($updateUserRequest);
        } else {
            $response = new GenericResponse();
            $response->setMessage("Please provide a valid id parameter");
            $response->statusCode = 422;
        }

        $serializedResponse = $serializer->serialize($response, 'json', [AbstractObjectNormalizer::SKIP_NULL_VALUES => true]);
        return new Response($serializedResponse, $response->statusCode, ['Content-Type' => "text/json"]);
    }

    #[Route('/api/simpleLoggedAction', name: 'api.simpleLoggedAction', methods: ['GET'])]
    public function loggedAction(): JsonResponse
    {
        return new JsonResponse(null, 200);
    }
}
