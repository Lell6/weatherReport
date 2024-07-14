<?php

class Database {
    public function saveRecordToDatabase($data) {
        require_once '../Entities/WeatherRecords.php';
        require_once '../doctrine-config.php';

        $record = new WeatherRecords($data);

        $entityManager->persist($record);
        $entityManager->flush();
    }
}