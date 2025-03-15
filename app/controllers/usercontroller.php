<?php

namespace Controllers;

use Exception;
use Services\UserService;
use \Firebase\JWT\JWT;

class UserController extends Controller
{
    private $service;

    // initialize services
    function __construct()
    {
        $this->service = new UserService();
    }

    public function login()
    {
        $loginDTO = $this->createObjectFromPostedJson("Models\\LoginDTO");
        $user = $this->service->checkUsernamePassword($loginDTO->getEmail(), $loginDTO->getPassword());

        if (!$user) {
            $this->respondWithError(401, 'Invalid email or password.');
            return;
        }

        $tokenresponse = $this->generateJWT($user);
        $this->respond($tokenresponse);
    }

    private function generateJWT($user)
    {

        $issuedAt = time();
        $expirationTime = $issuedAt + (60 * 60);

        $payload = [
            "user_id" => $user->getId(),
            "email" => $user->getEmail(),
            "role" => $user->getRole(),
            "iat" => $issuedAt,
            "exp" => $expirationTime
        ];

        $jwt = JWT::encode($payload, $this->secretKey, 'HS256');

        return [
            "message" => "Successful login",
            "token" => $jwt,
            "user_id" => $user->getId(),
            "email" => $user->getEmail(),
            "role" => $user->getRole(),
            "expires_in" => $expirationTime - $issuedAt
        ];
    }

    public function create()
    {
        try {
            $user = $this->authenticate();
            if ($user->role !== "admin") {
                $this->respondWithError(403, "Forbidden: Only admins can create users.");
                return;
            }
            $user = $this->createObjectFromPostedJson("Models\\User");
            $user = $this->service->insert($user);

        } catch (Exception $e) {
            $this->respondWithError(500, $e->getMessage());
        }

        $this->respond($user);
    }

    public function register()
    {
        try {
            $registerDTO = $this->createObjectFromPostedJson("Models\\RegisterDTO");
            $user = $this->service->register($registerDTO);

            if ($user) {
                $this->respond(["message" => "User registered successfully!", "user" => $user]);
            } else {
                $this->respondWithError(500, "User registration failed.");
            }
        } catch (Exception $e) {
            $this->respondWithError(500, $e->getMessage());
        }
    }

    public function getOne()
    {
        try {
            $user = $this->authenticate();
            $user = $this->service->getOne($user->user_id);
            $this->respond($user);
        } catch (Exception $e) {
            $this->respondWithError(500, $e->getMessage());
        }
    }


}
