<?php

use Slim\Views\Twig;
use Slim\Views\TwigMiddleware;

class Weather {
    private $pdo;
    private $userIp;
    private $userLocation;
    private $city = "London";
    public $weatherReport;

    public function setDatabaseConnection($app) {
        $this->pdo = $app->getContainer()->get('db');
    }

    public function getWeatherApi($errors = null) {
        $data = [$this->userLocations, $this->weatherReport, $errors];

        return $data;
    }

    public function getUserLocation($userIp) {
        if (empty($userIp)) {
            return false;
        }

        $location = file_get_contents('https://api.ip2location.io/?key=64B3CBADF317CD0ED2E49732490A9E10&ip='.$userIp);
        $location = json_decode($location, true);

        $this->userLocation = [
            'city' => $location['city_name'],
            'longitude' => $location['longitude'],
            'latitude' => $location['latitude']
        ];

        $this->userIp = $userIp;
        $this->city = $this->userLocation['city'];
        return true;
    }

    public function getWeatherReport($city = null) {
        if($city) {
            $this->city = $city;
        }

        $apiKey = "ca662e857d69444d99b85300240907";
        $numberOfDays = 1;

        $url = "https://api.weatherapi.com/v1/forecast.json?key={$apiKey}&q={$this->city}&days={$numberOfDays}&aqi=yes&alerts=no";    
        $weatherData = file_get_contents($url);    
        $weatherData = json_decode($weatherData, true);
        
        if (empty($weatherData)) {
            return false;
        }
    
        $this->weatherReport = [
            'date' => $weatherData['current']['last_updated'],
            'temperature' => $weatherData['current']['temp_c'],
            'condition' => $condition = $weatherData['current']['condition']['text'],
            'windType' => $weatherData['current']['wind_dir'],
            'windSpeed' => $weatherData['current']['wind_mph']
        ];

        if ($city) {
            $this->city = $weatherData['location']['name'];
            $this->userLocation['longitude'] = $weatherData['location']['lon'];
            $this->userLocation['latitude'] = $weatherData['location']['lat'];
        }

        return true;
    }

    public function saveRecordToDatabase() {
        $query = "INSERT 
        INTO `weatherdata` (`userIp`, `city`, `date`, `temperature`, `weatherCondition`, `windType`, `windSpeed`)
        VALUES (:ip, :city, :date, :temp, :cond, :wType, :wSpeed)";
    
        $query = $this->pdo->prepare($query);
        $query->execute([
            ':ip' => $this->userIp,
            ':city' => $this->city,
            ':date' => $this->weatherReport['date'],
            ':temp' => $this->weatherReport['temperature'],
            ':cond' => $this->weatherReport['condition'],
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
            'condition' => $this->weatherReport['condition'],
            'windType' => $this->weatherReport['windType'],
            'windSpeed' => $this->weatherReport['windSpeed'],
            'longitude' => $this->userLocation['longitude'],
            'latitude' => $this->userLocation['latitude'],
            'error' => $errorContents
        ]);
    }
};