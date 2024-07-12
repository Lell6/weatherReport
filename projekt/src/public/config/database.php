<?php

class Database {
    public function saveRecordToDatabase() {
        //blędy bazy danych
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
    }
}

$container->loadFromExtension('doctrine', [
    'dbal' => [
        'driver' => 'pdo_mysql',
        'host' => '127.0.0.1',
        'dbname' => 'weatherreport',
        'user' => 'root',
        'password' => '',
    ],
]);