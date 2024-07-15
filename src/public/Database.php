<?php

class Database {
    private $entityManager;

    function __construct($entityManager) {
        $this->entityManager = $entityManager;
    }

    public function saveRecordToDatabase($data) {
        require_once '../Entities/WeatherRecords.php';

        $record = new WeatherRecords($data);

        $this->entityManager->persist($record);
        $this->entityManager->flush();
    }

    public function findUserByLogin($login) {
        require_once '../Entities/Users.php';

        $userRepository = $this->entityManager->getRepository(Users::class);
        return $userRepository->findOneBy(['login' => $login]);
    }

    public function findUserByToken($token) {
        require_once '../Entities/Users.php';

        $userRepository = $this->entityManager->getRepository(Users::class);
        return $userRepository->findOneBy(['token' => $token]);
    }

    public function createUser($data) {
        require_once '../Entities/Users.php';
        
        $user = new Users($data);       

        $this->entityManager->persist($user);
        $this->entityManager->flush();
    }
}