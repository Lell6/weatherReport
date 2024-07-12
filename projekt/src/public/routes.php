<?php
use Dotenv\Dotenv;
use Slim\Factory\AppFactory;
use Slim\Views\Twig;
use Slim\Views\TwigMiddleware;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

require __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

require 'config/Weather.php';
require 'config/UserIp.php';
require 'config/UserLocation.php';

$twig = Twig::create('templates/', ['cache' => false]);
$app = AppFactory::create();
$app->add(TwigMiddleware::create($app, $twig));

$app->get('/weather/[{city}]', function (Request $request, Response $response, $args) use ($twig, $app) {
    $city = $args['city'] ?? null;

    $location = new UserLocation();
    $weather = new Weather();

    $location->setUserLocationByCity($city);
    $weather->setWeatherReport($location->getUserLocation());
    $data = $weather->getWeatherReport();

    $response->getBody()->write(json_encode($data));
    return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
});

$app->get('/weather', function (Request $request, Response $response, $args) use ($twig, $app) {
    $ip = new UserIp();
    $location = new UserLocation();
    $weather = new Weather();
    //$weatherService->setDatabaseConnection($app);

    $ip->setUserIp();
    $location->setUserLocationByIp($ip->getUserIp());
    $weather->setWeatherReport($location->getUserLocation());

    $weatherReport = $weather->getWeatherReport();
    $userLocation = $location->getUserLocation();

    //$status = $weatherService->saveRecordToDatabase();

    $view = Twig::fromRequest($request);
    return $view->render($response, 'weatherReport.html', [
        'temperature' => $weatherReport['temperature'], 
        'description' => $weatherReport['description'],
        'windType' => $weatherReport['windType'],
        'windSpeed' => $weatherReport['windSpeed'],
        'cityName' => $userLocation['city'], 
        'longitude' => $userLocation['longitude'],
        'latitude' => $userLocation['latitude']
    ]);
});

return $app;