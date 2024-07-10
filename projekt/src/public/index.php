<?php
use Slim\Factory\AppFactory;
use DI\Container;

require __DIR__ . '/../vendor/autoload.php';

$container = new Container();
AppFactory::setContainer($container);

$dependencies = require __DIR__ . '/dependencies.php';
$dependencies($container);

$app = AppFactory::create();

$app = require __DIR__ . '/routes.php';

$app->run();