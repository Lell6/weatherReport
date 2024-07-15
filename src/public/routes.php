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
require 'Database.php';
require '../doctrine-config.php';

$twig = Twig::create('templates/', ['cache' => false]);
$app = AppFactory::create();
$app->add(TwigMiddleware::create($app, $twig));

$app->get('/weather', function (Request $request, Response $response, $args) use ($twig, $app, $entityManager) {
    $city = $request->getQueryParams()['city'] ?? null;
    $key = $request->getQueryParams()['key'] ?? null;

    $database = new Database($entityManager);
    $user = $database->findUserByToken($key);

    if (!$user) {
        $data = [
            'message' => "Incorrect key"
        ];
  
        $response->getBody()->write(json_encode($data));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    }

    $location = new UserLocation();
    $weather = new Weather();

    $location->setUserLocationByCity($city);
    $weather->setWeatherReport($location->getUserLocation());

    if ($location->getUserLocation()) {
        $data = [
            'location' => $location->getUserLocation(),
            'weather' => $weather->getWeatherReport()
        ];

        $database->saveRecordToDatabase($data);
    }
    else {
        $data = $weather->getWeatherReport();
    }

    $response->getBody()->write(json_encode($data));
    return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
});

$app->get('/weather/page', function (Request $request, Response $response, $args) use ($twig, $app, $entityManager) {
    $ip = new UserIp();
    $location = new UserLocation();
    $weather = new Weather();

    $ip->setUserIp();
    $location->setUserLocationByIp($ip->getUserIp());
    $weather->setWeatherReport($location->getUserLocation());

    $weatherReport = $weather->getWeatherReport();
    $userLocation = $location->getUserLocation();

    if ($userLocation) {
        $data = [
            'location' => $userLocation,
            'weather' => $weatherReport
        ];

        $database = new Database($entityManager);
        $database->saveRecordToDatabase($data);
    }

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

$app->get('/register', function (Request $request, Response $response, $args) use ($twig, $app) {
    $view = Twig::fromRequest($request);
    return $view->render($response, 'register.html', []);
});

$app->post('/register', function (Request $request, Response $response, $args) use ($twig, $app, $entityManager) {
    $data = $request->getParsedBody();

    $login = $data['login'];
    $password = $data['password'];
    $passwordRepeat = $data['passwordRepeat'];
    $view = Twig::fromRequest($request);

    if ($password != $passwordRepeat) {
        return $view->render($response, 'register.html', [
            'errors' => 'Passwords must repeat'
        ]);
    }

    $database = new Database($entityManager);
    $user = $database->findUserByLogin($login);
    
    if ($user) {
        return $view->render($response, 'register.html', [
            'errors' => 'User already exists'
        ]);
    }
    
    $database->createUser($data);
    return $view->render($response, 'register.html', []);
});

$app->get('/login', function (Request $request, Response $response, $args) use ($twig, $app) {
    $view = Twig::fromRequest($request);
    return $view->render($response, 'login.html', []);
});

$app->post('/login', function (Request $request, Response $response, $args) use ($twig, $app, $entityManager) {
    $view = Twig::fromRequest($request);
    $data = $request->getParsedBody();
    $database = new Database($entityManager);

    $user = $database->findUserByLogin($data['login']);

    if (!$user) {
        return $view->render($response, 'login.html', [
            'errors' => 'Incorrect login or password'
        ]);
    }

    $password = $user->getPassword();
    if (!password_verify($data['password'], $password)) {
        return $view->render($response, 'login.html', [
            'errors' => 'Incorrect login or password'
        ]);
    }
    else {
        return $view->render($response, 'userData.html', [
            'login' => $data['login'],
            'key' => $user->getToken()
        ]);
    }
});

return $app;