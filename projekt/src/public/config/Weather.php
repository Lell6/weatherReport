<?php

use Slim\Views\Twig;
use Slim\Views\TwigMiddleware;

class Weather {
    private $pdo;
    private $userIp;
    private $userLocation;
    private $city;
    public $weatherReport;

    public function setDatabaseConnection($app) {
        $this->pdo = $app->getContainer()->get('db');
    }

    public function getWeatherApi($errors = null) {
        $data = [
            'location' => $this->userLocation, 
            'weatherReport' => $this->weatherReport, 
            'errors' => $errors
        ];

        return $data;
    }

    public function getUserIpAddress() {
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

    public function getUserLocation() {
        if (empty($this->userIp)) {
            return false;
        }

        $location = file_get_contents('https://api.ip2location.io/?key=64B3CBADF317CD0ED2E49732490A9E10&ip='.$this->userIp);
        $location = json_decode($location, true);

        $this->userLocation = [
            'city' => $location['city_name'],
            'longitude' => $location['longitude'],
            'latitude' => $location['latitude']
        ];

        $this->city = $this->userLocation['city'];
        return true;
    }

    public function getWeatherReport($city = null) {
        if($city) {
            $this->city = $city;
        }

        $apiKey = "a265896dac8a519de32f3924f10dde01";

        $url = "https://api.openweathermap.org/data/2.5/weather?q={$this->city}&appid={$apiKey}&units=metric";    
        $weatherData = file_get_contents($url);    
        $weatherData = json_decode($weatherData, true);
        
        if (empty($weatherData)) {
            return false;
        }
    
        $this->weatherReport = [
            'temperature' => $weatherData['main']['temp'],
            'shortDescr' => $weatherData['weather'][0]['main'],
            'description' => $weatherData['weather'][0]['description'],
            'windType' => $weatherData['wind']['deg'],
            'windSpeed' => $weatherData['wind']['speed']
        ];

        $this->userLocation['longitude'] = $weatherData['coord']['lon'];
        $this->userLocation['latitude'] = $weatherData['coord']['lat'];

        return true;
    }

    public function saveRecordToDatabase() {
        $query = "INSERT 
        INTO `weatherdata` (`userIp`, `city`, `date`, `temperature`, `weatherdescription`, `windType`, `windSpeed`)
        VALUES (:ip, :city, :date, :temp, :cond, :wType, :wSpeed)";
    
        $query = $this->pdo->prepare($query);
        $query->execute([
            ':ip' => $this->userIp,
            ':city' => $this->city,
            ':date' => $this->weatherReport['date'],
            ':temp' => $this->weatherReport['temperature'],
            ':cond' => $this->weatherReport['description'],
            ':wType' => $this->weatherReport['windType'],
            ':wSpeed' => $this->weatherReport['windSpeed']
        ]);
    
        return true;
    }

    public function printPage($request, $response, $errorContents = "") {
        $view = Twig::fromRequest($request);

        return $view->render($response, 'weatherReport.html', [
            'cityName' => $this->city, 
            'date' => $this->weatherReport['date'], 
            'temperature' => $this->weatherReport['temperature'], 
            'description' => $this->weatherReport['description'],
            'windType' => $this->weatherReport['windType'],
            'windSpeed' => $this->weatherReport['windSpeed'],
            'longitude' => $this->userLocation['longitude'],
            'latitude' => $this->userLocation['latitude'],
            'error' => $errorContents
        ]);
    }
};