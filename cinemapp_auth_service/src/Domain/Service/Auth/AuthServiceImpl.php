<?php

namespace Domain\Service\Auth;

use Domain\Entity\User;
use Domain\Request\LoginRequest;
use Domain\Response\Auth\LoginResponse;
use Domain\Response\GenericResponse;
use Infrastructure\Symfony\Repository\Auth\AuthRepositoryInterface;
use Infrastructure\Symfony\Repository\User\UserRepositoryInterface;

class AuthServiceImpl implements AuthServiceInterface
{

    public function __construct(
        // Dep inject
        protected UserRepositoryInterface $userRepository,
        protected AuthRepositoryInterface $authRepository,

        // From yaml config
        protected string $appSecret,

        // Pure
        protected int $lockExpirationDuration = (60 * 30), // 30 minutes lock

        // 4 login fail within 5 minutes --> lock
        protected int $allowedFailAttemptNb = 4,
        protected int $failAttemptRangeInMinute = 5
    ) {
    }

    public function login(LoginRequest $request): LoginResponse
    {

        $response = new LoginResponse();

        $creds = $request->getLoginCredentials();

        $clientIp = $request->getClientIp();

        if (
            ($creds->email === null || trim($creds->email) === '') ||
            ($creds->password === null || trim($creds->password) === '')
        ) {
            $response->setValidationError();
            $response->addFieldErrorMessage('credentials', 'please provide a valid email:password combo');
            return $response;
        }

        try {
            $user = $this->userRepository->getUserByEmail($creds->email);

            if (is_null($user) || !$user->status) {
                $response->userNotFound();
                return $response;
            }

            if ($this->isUserLocked($user->id)) {
                $response->userLocked();
                return $response;
            }

            if (password_verify($creds->password, $user->password)) {
                $access_jwt = $this->generateJWT($user, "access");
                $refresh_jwt = $this->generateJWT($user, "refresh");

                $response->logged($access_jwt, $refresh_jwt);
            } else {

                $this->authRepository->newLoginAttemptFailed($user->id, $clientIp);

                $needToBeLocked = $this->isUserNeedToBeLocked($user->id, $clientIp);

                if ($needToBeLocked) {

                    $expireAt = time() + $this->lockExpirationDuration;

                    $this->authRepository->lockUser($user->id, $expireAt);
                }

                $response->loginError();
            };
        } catch (\Exception $e) {
            $response->setException($e);
            return $response;
        }

        return $response;
    }

    public function refreshToken(string $refreshToken): LoginResponse
    {
        $response = new LoginResponse();

        [$header, $payload, $signatureFromUser] = explode(".", $refreshToken);

        $signatureFromSystem = hash_hmac('sha256', "$header.$payload", $this->appSecret);

        if ($signatureFromSystem === $signatureFromUser) {
            $decodedPayload = base64_decode($payload);

            if (json_validate($decodedPayload)) {
                $decodedPayload = json_decode($decodedPayload, true);

                $user = $this->userRepository->getUserByEmail($decodedPayload['user_email']);

                if (!is_null($user) && time() < $decodedPayload['expire_at'] && $decodedPayload['type'] === "refresh") {
                    $access_jwt = $this->generateJWT($user, "access");
                    $refresh_jwt = $this->generateJWT($user, "refresh");

                    $response->logged($access_jwt, $refresh_jwt);
                    return $response;
                }
            }
        }

        $response->setMessage("Invalid or non-existent token");
        $response->statusCode = 404;
        return $response;
    }

    public function validateToken(string $token): GenericResponse
    {
        $response = new GenericResponse();

        try {
            [$header, $payload, $signatureFromUser] = explode(".", $token);

            $signatureFromSystem = hash_hmac('sha256', "$header.$payload", $this->appSecret);

            if ($signatureFromSystem === $signatureFromUser) {

                $decodedPayload = base64_decode($payload);

                if (json_validate($decodedPayload)) {
                    $decodedPayload = json_decode($decodedPayload, true);

                    if ($decodedPayload["type"] === "access" || time() < $decodedPayload['expire_at']) {
                        $response->setData([
                            "auth" => [
                                "accessToken" => $token,
                                "accessTokenExpireAt" => new \DateTime(date('Y-m-d H:i:s', $decodedPayload['expire_at']), new \DateTimeZone('Europe/Paris'))
                            ]
                        ]);
                        $response->fetchOk();

                        return $response;
                    }
                }
            }
        } catch (\Throwable $th) {}

        $response->notFound();
        $response->setMessage("Token not found or invalid");

        return $response;
    }

    private function isUserLocked(int $userId): bool
    {
        $lock = $this->authRepository->getUserLock($userId);

        if (isset($lock[0])) return true;

        return false;
    }

    private function isUserNeedToBeLocked(int $userId, ?string $clientIp): bool
    {
        $lastFailedLoginAttempts = $this->authRepository->getLastLoginAttemptsFailed($userId, $clientIp, $this->allowedFailAttemptNb);

        if (count($lastFailedLoginAttempts) == $this->allowedFailAttemptNb) {
            $firstAttemptAt = $lastFailedLoginAttempts[$this->allowedFailAttemptNb - 1]['created_at'];
            $lastAttemptAt = $lastFailedLoginAttempts[0]['created_at'];

            $firstAttemptAtTimestamp = strtotime($firstAttemptAt);
            $lastAttemptAtTimestamp = strtotime($lastAttemptAt);

            $failTimeRangeMinutes = ($lastAttemptAtTimestamp - $firstAttemptAtTimestamp) / 60;

            if ($failTimeRangeMinutes < $this->failAttemptRangeInMinute) {
                return true;
            }
        }

        return false;
    }

    private function generateJWT(User $user, string $type): object
    {
        $header = base64_encode(json_encode([
            "alg" => "HS256",
            "typ" => "JWT"
        ]));

        $now = time();

        $exp_factor = 60 * ($type === "access" ? 60 : 120);

        $exp = $now + $exp_factor;

        $payload = base64_encode(json_encode([
            "type" => $type,
            "user_email" => $user->email,
            "user_id" => $user->id,
            "user_roles" => $user->getRoles(),
            "created_at" => $now,
            "expire_at" => $exp
        ]));

        $signature = hash_hmac('sha256', "$header.$payload", $this->appSecret);

        $jwt = "$header.$payload.$signature";

        return (object) [
            "token" => $jwt,
            "expire_at" => new \DateTime(date('Y-m-d H:i:s', $exp), new \DateTimeZone('Europe/Paris'))
        ];
    }
}
