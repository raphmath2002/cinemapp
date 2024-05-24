<?php

namespace Infrastructure\Symfony\Security;

use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;

class ApiKeyAuthenticator extends AbstractAuthenticator
{

    public function __construct(
        protected string $appSecret,
        protected Security $security
    ) {
    }

    public function supports(Request $request): ?bool
    {
        return $request->headers->has('Authorization');
    }

    public function authenticate(Request $request): Passport
    {
        $apiToken = $request->headers->get('Authorization');

        if (is_null($apiToken) || !str_contains($apiToken, 'Bearer')) $this->notValidToken();

        $apiToken = trim(str_replace('Bearer', '', $apiToken));

        if ($apiToken === '') $this->notValidToken();

        [$header, $payload, $signatureFromUser] = explode(".", $apiToken);

        $signatureFromSystem = hash_hmac('sha256', "$header.$payload", $this->appSecret);

        if ($signatureFromSystem === $signatureFromUser) {

            $decodedPayload = base64_decode($payload);

            if (json_validate($decodedPayload)) {
                $decodedPayload = json_decode($decodedPayload, true);

                if ($decodedPayload["type"] != "access") $this->notValidToken();

                if (time() < $decodedPayload['expire_at']) {
                    return new SelfValidatingPassport(new UserBadge($decodedPayload['user_email']));
                }
            }
        }

        $this->notValidToken();
    }

    private function notValidToken()
    {
        throw new CustomUserMessageAuthenticationException("No valid Bearer API token provided");
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        return null;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        $data = [
            // you may want to customize or obfuscate the message first
            'message' => strtr($exception->getMessageKey(), $exception->getMessageData())
            // or to translate this message
            // $this->translator->trans($exception->getMessageKey(), $exception->getMessageData())
        ];

        return new JsonResponse($data, Response::HTTP_UNAUTHORIZED);
    }
}
