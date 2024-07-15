<?php

use Doctrine\ORM\Mapping as ORM;
use Doctrine\DBAL\Types\Types;

#[ORM\Entity]
#[ORM\Table(name: "weather_records")]
class WeatherRecords {
    #[ORM\Id, ORM\Column(type: Types::INTEGER), ORM\GeneratedValue]
    private ?int $id = null;

    #[ORM\Column(type: Types::STRING, length: 16)]
    private ?string $userIp = null;

    #[ORM\Column(type: Types::STRING, length: 85)]
    private ?string $userCity = null;

    #[ORM\Column(type: Types::STRING, length: 7)]
    private ?string $longitude = null;

    #[ORM\Column(type: Types::STRING, length: 7)]
    private ?string $latitude = null;

    #[ORM\Column(type: Types::STRING, length: 7)]
    private ?string $temperature = null;

    #[ORM\Column(type: Types::STRING, length: 30)]
    private ?string $shortDescrt = null;

    #[ORM\Column(type: Types::STRING, length: 30)]
    private ?string $description = null;
    
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $windType = null;
    
    #[ORM\Column(type: Types::STRING, length: 5)]
    private ?string $windSpeed = null;

    function __construct($record) {
        $this->userIp = (isset($record['location']['ip'])) ? $record['location']['ip'] : '0.0.0.0';
        $this->userCity = $record['location']['city'];
        $this->longitude = $record['location']['longitude'];
        $this->latitude = $record['location']['latitude'];
        $this->temperature = $record['weather']['temperature'];
        $this->shortDescrt = $record['weather']['shortDescr'];
        $this->description = $record['weather']['description'];
        $this->windType = $record['weather']['windType'];
        $this->windSpeed = $record['weather']['windSpeed'];
    }
}