<?php
use Slim\Factory\AppFactory;
use Slim\Views\Twig;
use Slim\Views\TwigMiddleware;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

require __DIR__ . '/../vendor/autoload.php';

require 'config/Weather.php';

$twig = Twig::create('templates/', ['cache' => false]);
$app = AppFactory::create();
$app->add(TwigMiddleware::create($app, $twig));

$app->get('/weather/', function (Request $request, Response $response, $args) use ($twig, $app) {
    $weatherService = new Weather();
    $errorContents = "Nie podano miasta";

    $status = $weatherService->getWeatherReport();
    $data =  $weatherService->getWeatherApi($errorContents);

    $response->getBody()->write(json_encode($data));
    return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
});

$app->get('/weather/{city}', function (Request $request, Response $response, $args) use ($twig, $app) {
    $weatherService = new Weather();

    $data = $request->getParsedBody();
    $city = $args['city'];

    $status = $weatherService->getWeatherReport($city);
    $data =  $weatherService->getWeatherApi();

    $response->getBody()->write(json_encode($data));
    return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
});

$app->get('/weather', function (Request $request, Response $response, $args) use ($twig, $app) {
    $weatherService = new Weather();
    $weatherService->setDatabaseConnection($app);

    $weatherService->getUserIpAddress();
    $status = $weatherService->getUserLocation();

    if (!$status) {
        return $weatherService->printPage($request, $response, "Error - no user");
    }

    $status = $weatherService->getWeatherReport();

    if (!$status) {
        return $weatherService->printPage($request, $response, "Error - no weather");
    }

    //$status = $weatherService->saveRecordToDatabase();

    if (!$status) {
        return $weatherService->printPage($request, $response, "Error - no base");
    }

    return $weatherService->printPage($request, $response);
});

return $app;