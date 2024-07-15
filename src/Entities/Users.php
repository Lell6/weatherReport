<?php

use Doctrine\ORM\Mapping as ORM;
use Doctrine\DBAL\Types\Types;

#[ORM\Entity]
#[ORM\Table(name: "users")]
class Users {
    #[ORM\Id, ORM\Column(type: Types::INTEGER), ORM\GeneratedValue]
    private ?int $userId = null;

    #[ORM\Column(type: Types::STRING, length: 20)]
    private ?string $login = null;

    #[ORM\Column(type: Types::STRING, length: 64)]
    private ?string $password = null;

    #[ORM\Column(type: Types::STRING, length: 32)]
    private ?string $token = null;

    function __construct($data) {
        $this->login = $data['login'];
        $this->password = password_hash($data['password'], PASSWORD_DEFAULT);
        $this->token = bin2hex(random_bytes(16));
    }

    public function getPassword() {
        return $this->password;
    }

    public function getToken() {
        return $this->token;
    }

    public function getLogin() {
        return $this->login;
    }
}