<?php

use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMSetup;

require_once "vendor/autoload.php";

$config = ORMSetup::createAttributeMetadataConfiguration(
    paths: [__DIR__ . "/Entities"],
    isDevMode: true,
);

$connectionParams = [
    'driver'   => 'pdo_mysql',
    'user'     => 'root',
    'password' => '',
    'dbname'   => 'weatherreport',
];

$conn = DriverManager::getConnection($connectionParams, $config);
$entityManager = new EntityManager($conn, $config);

return $entityManager;