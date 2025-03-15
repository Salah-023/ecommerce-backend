<?php
namespace Services;

use Models\User;
use Models\RegisterDTO;
use Repositories\UserRepository;

class UserService {

    private $repository;

    function __construct()
    {
        $this->repository = new UserRepository();
    }

    public function checkUsernamePassword($email, $password) {
        return $this->repository->checkUsernamePassword($email, $password);
    }

    public function insert($user){
        return $this->repository->insert($user);
    }

    public function register(RegisterDTO $registerDTO){
        $user = new User(
            $registerDTO->getName(), 
            $registerDTO->getEmail(), 
            $registerDTO->getPassword(), 
            'customer' // Default role
        );
        return $this->repository->insert($user);
    }

    public function getOne(int $id): ?User {
        return $this->repository->getOne($id);
    }
    
}

?>