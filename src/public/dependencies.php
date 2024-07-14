<?php

return function (\DI\Container $container) {
    $container->set('logger', function() {
        $logger = new \Monolog\Logger('slim-app');
        $file_handler = new \Monolog\Handler\StreamHandler(__DIR__ . '/../logs/app.log');
        $logger->pushHandler($file_handler);
        return $logger;
    });

    $container->set('db', function() {
        $host = '127.0.0.1';
        $dbname = 'weatherreport';
        $username = 'root';
        $password = '';
        $dsn = "mysql:host=$host;dbname=$dbname;charset=UTF8";

        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];

        return new PDO($dsn, $username, $password, $options);
    });
};