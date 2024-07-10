<?php
use Slim\Factory\AppFactory;
use Slim\Views\Twig;
use Slim\Views\TwigMiddleware;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

require __DIR__ . '/../vendor/autoload.php';

$twig = Twig::create('templates/', ['cache' => false]);
$app = AppFactory::create();
$app->add(TwigMiddleware::create($app, $twig));

$app->get('/', function (Request $request, Response $response) {
    $response->getBody()->write("Hello, world!");
    return $response;
});

$app->get('/weather', function (Request $request, Response $response, $args) use ($twig, $app) {
    $pdo = $app->getContainer()->get('db');

    $userIp = $_SERVER['HTTP_CLIENT_IP'];
    $userLocation = var_export(unserialize(file_get_contents('http://www.geoplugin.net/php.gp?ip='.$userIp)));

    $userIp = $userLocation['geoplugin_request'];
    $latitude = $userLocation['geoplugin_latitude'];
    $longitude = $userLocation['geoplugin_longitude'];
    
    $city = $userLocation['geoplugin_city'];
    $apiKey = "ca662e857d69444d99b85300240907";
    $numberOfDays = 1;

    $url = "https://api.weatherapi.com/v1/forecast.json?key={$apiKey}&q={$city}&days={$numberOfDays}&aqi=yes&alerts=no";    
    $apiResponse = file_get_contents($url);

    $weatherData = json_decode($apiResponse, true);
    $date = $weatherData['current']['last_updated'];
    $temperature = $weatherData['current']['temp_c'];
    $condition = $weatherData['current']['condition']['text'];
    $windType = $weatherData['current']['wind_dir'];
    $windSpeed = $weatherData['current']['wind_mph'];
    error_log("User IP: " . $userIp);

    $query = "INSERT 
                INTO `weatherdata` (`userIp`, `city`, `date`, `temperature`, `weatherCondition`, `windType`, `windSpeed`)
                VALUES (:ip, :city, :date, :temp, :cond, :wType, :wSpeed)";

    $query = $pdo->prepare($query);
    $query->execute([
        ':ip' => $userIp,
        ':city' => $city,
        ':date' => $date,
        ':temp' => $temperature,
        ':cond' => $condition,
        ':wType' => $windType,
        ':wSpeed' => $windSpeed
    ]);

    $view = Twig::fromRequest($request);
    return $view->render($response, 'weatherReport.html', [
        'cityName' => $city, 
        'date' => $date, 
        'temperature' => $temperature, 
        'condition' => $condition,
        'windType' => $windType,
        'windSpeed' => $windSpeed,
        'longitude' => $longitude,
        'latitude' => $latitude
    ]);
});

return $app;