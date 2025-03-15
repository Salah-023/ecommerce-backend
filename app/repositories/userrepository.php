<?php

namespace Repositories;

use PDO;
use PDOException;
use Exception;
use Repositories\Repository;
use Models\User;

class UserRepository extends Repository
{
    public function checkUsernamePassword($email, $password)
{
    try {
        // Retrieve the user with the given email
        $stmt = $this->connection->prepare("SELECT * FROM users WHERE email = :email");
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt->execute();

        // Fetch user data as an associative array
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row)  return false; // No user found

        // Manually create User object (ensures constructor is used)
        $user = new User($row['name'], $row['email'], $row['password'], $row['role'], $row['created_at']);
        $user->setId($row['id']);
        // Verify if the password matches the hash in the database
        if (!password_verify($password, $user->getPassword())) {
            return false;
        }

        // Do not pass the password hash to the caller
        $user->setPassword("");

        return $user;

    } catch (PDOException $e) {
        error_log("Database error: " . $e->getMessage()); // Log error instead of exposing it
        return false;
    }
}


    // hash the password (currently uses bcrypt)
    function hashPassword($password)
    {
        return password_hash($password, PASSWORD_DEFAULT);
    }

    // verify the password hash
    function verifyPassword($input, $hash)
    {
        return password_verify($input, $hash);
    }

    public function insert($user)
    {
        try {
            // Check if email already exists
            $stmt = $this->connection->prepare("SELECT id FROM users WHERE email = :email");
            $stmt->bindValue(':email', $user->getEmail(), PDO::PARAM_STR);
            $stmt->execute();
    
            if ($stmt->fetch()) {
                throw new Exception("Email already in use.");
            }
    
            // Insert new user with hashed password
            $stmt = $this->connection->prepare("
                INSERT INTO users (name, email, password, role) 
                VALUES (:name, :email, :password, :role)
            ");
    
            $hashedPassword = password_hash($user->getPassword(), PASSWORD_DEFAULT);

            $stmt->bindValue(':name', $user->getName(), PDO::PARAM_STR);
            $stmt->bindValue(':role', $user->getRole(), PDO::PARAM_STR);            
            $stmt->bindValue(':email', $user->getEmail(), PDO::PARAM_STR);
            $stmt->bindValue(':password', $hashedPassword, PDO::PARAM_STR);
    
            $stmt->execute();
    
            // Ensure insert was successful
            if ($stmt->rowCount() > 0) {
                $user->setId($this->connection->lastInsertId());
                $user->setPassword(""); // Do not return password hash
                return $user;
            } else {
                throw new Exception("User insertion failed.");
            }
    
        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            return null;
        } catch (Exception $e) {
            error_log("Error: " . $e->getMessage());
            return null;
        }
    }

    public function getOne(int $id): ?User {
        try {
            $stmt = $this->connection->prepare("SELECT * FROM users WHERE id = :id");
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();

            $userData = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$userData) return null;
            $user = new User($userData['name'],$userData['email'],"", $userData['role'],$userData['created_at']);
            $user->setId($userData['id']);
            
            return $user;
        } catch (Exception $e) {
            error_log("Error fetching user: " . $e->getMessage());
            return null;
        }
    }
    
}
