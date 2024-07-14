<?php

class UserIp {
    private $userIp;
    public $errors;

    public function setUserIp() {
        if(!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $this->userIp = $_SERVER['HTTP_CLIENT_IP'];  
        }
        elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $this->userIp = $_SERVER['HTTP_X_FORWARDED_FOR'];  
        }
        else{  
            $this->userIp = "46.205.198.246";  
        }
    }

    public function getUserIp() {
        return $this->userIp;
    }
}