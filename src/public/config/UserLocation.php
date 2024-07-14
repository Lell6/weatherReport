<?php

class UserLocation {
    private $userLocation;
    public $errors;

    public function setUserLocationByIp($userIp) {
        $url = htmlspecialchars_decode("{$_ENV['location_api_ip_url']}?key={$_ENV['location_api_ip_key']}&ip=".$userIp);

        $location = file_get_contents($url);
        $location = json_decode($location, true);
    
        $this->userLocation = [
            'ip' => $userIp,
            'city' => $location['city_name'],
            'longitude' => $location['longitude'],
            'latitude' => $location['latitude']
        ];
    }

    public function setUserLocationByCity($city = null) {
        if (!$city) {
            $this->errors = [
                'message' => "No city selected"
            ];
            
            return;
        }

        $url = htmlspecialchars_decode("{$_ENV['location_api_city_url']}?q={$city}&appid={$_ENV['location_api_city_key']}");

        $location = file_get_contents($url);
        $location = json_decode($location, true);

        if (!$location) {
            $this->errors = [
                'message' => 'City not exists'
            ];

            return;
        }

        $this->userLocation = [
            'city' => $location[0]['name'],
            'longitude' => number_format($location[0]['lon'], 3),
            'latitude' => number_format($location[0]['lat'], 3)
        ];
    }

    public function getUserLocation() {
        if ($this->userLocation) {
            return $this->userLocation;
        }
    }
}