<?php

return function (\DI\Container $container) {
    $container->set('logger', function() {
        $logger = new \Monolog\Logger('slim-app');
        $file_handler = new \Monolog\Handler\StreamHandler(__DIR__ . '/../logs/app.log');
        $logger->pushHandler($file_handler);
        return $logger;
    });

    $container->set('entityManager', function () {
        require '../doctrine-config.php';
        return $entityManager;
    });
};