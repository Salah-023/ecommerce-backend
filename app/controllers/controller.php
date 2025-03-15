<?php

namespace Controllers;

use Exception;
use Repositories\CategoryRepository;
use Repositories\ProductRepository;
use Repositories\UserRepository;
use Repositories\OrderRepository;
use \Firebase\JWT\JWT;
use \Firebase\JWT\Key;

class Controller
{
    protected $secretKey = "your_secret_key";

    protected function authenticate()
    {
        try {
            // Get Authorization header
            $headers = apache_request_headers();
            if (!isset($headers['Authorization'])) {
                $this->respondWithError(401, "Access Denied. No token provided.");
                exit;
            }

            // Extract token (Bearer Token)
            $authHeader = $headers['Authorization'];
            $token = str_replace("Bearer ", "", $authHeader);

            // Decode and verify token
            $decoded = JWT::decode($token, new Key($this->secretKey, 'HS256'));

            // Return decoded user info (includes role)
            return $decoded;

        } catch (Exception $e) {
            $this->respondWithError(401, "Invalid or expired token.");
            exit;
        }
    }


    function respond($data)
    {
        $this->respondWithCode(200, $data);
    }

    function respondWithError($httpcode, $message)
    {
        $data = array('errorMessage' => $message);
        $this->respondWithCode($httpcode, $data);
    }

    private function respondWithCode($httpcode, $data)
    {
        header('Content-Type: application/json; charset=utf-8');
        http_response_code($httpcode);
        echo json_encode($data);
    }

    public function createObjectFromPostedJson(string $className)
    {
        $json = file_get_contents('php://input');
        $data = json_decode($json, true); // Decode JSON as an associative array

        if (!$data) {
            throw new Exception("Invalid JSON data.");
        }

        // Use Reflection to dynamically get constructor parameters
        $reflectionClass = new \ReflectionClass($className);
        $constructor = $reflectionClass->getConstructor();
        $parameters = $constructor->getParameters();

        // Prepare arguments for the constructor
        $args = [];
        foreach ($parameters as $param) {
            $paramName = $param->getName();
            $paramType = $param->getType();

            if (array_key_exists($paramName, $data)) {
                // If the parameter is a class (e.g., Category, Order), fetch it using the corresponding repository
                if ($paramType && !$paramType->isBuiltin()) {
                    $repo = $this->getRepositoryForClass($paramType->getName());
                    $args[] = $repo ? $repo->getOne((int) $data[$paramName]) : null;
                } else {
                    // Use JSON data directly for primitive types
                    $args[] = $data[$paramName];
                }
            } elseif ($param->isOptional()) {
                $args[] = $param->getDefaultValue(); // Use default value if optional
            } else {
                throw new Exception("Missing required field: $paramName");
            }
        }

        // Dynamically instantiate the class with the correct arguments
        return $reflectionClass->newInstanceArgs($args);
    }

    private function getRepositoryForClass(string $className)
    {
        $repositories = [
            'Models\Category' => new CategoryRepository(),
            'Models\Order' => new OrderRepository(),
            'Models\Product' => new ProductRepository(),
            'Models\User' => new UserRepository(),
        ];

        return $repositories[$className] ?? null;
    }
}
