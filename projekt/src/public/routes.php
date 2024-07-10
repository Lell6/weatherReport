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

$app->get('/weather', function (Request $request, Response $response, $args) use ($twig, $app) {
    $weatherService = new Weather();
    $weatherService->setDatabaseConnection($app);

    $userIp = "46.205.198.246";
    $status = $weatherService->getUserLocation($userIp);

    if (!$status) {
        return $weatherService->printPage($request, $response, "Error - no user");
    }

    $status = $weatherService->getWeatherReport();

    if (!$status) {
        return $weatherService->printPage($request, $response, "Error - no weather");
    }

    $status = $weatherService->saveRecordToDatabase();

    if (!$status) {
        return $weatherService->printPage($request, $response, "Error - no base");
    }

    return $weatherService->printPage($request, $response);
});

$app->post('/weather', function (Request $request, Response $response, $args) use ($twig, $app) {
    $weatherService = new Weather();
    $weatherService->setDatabaseConnection($app);

    $data = $request->getParsedBody();
    $city = $data['city'];
    
    if (!$city) {
        $errorContents = "Nie podano miasta";
    }
    $status = $weatherService->getWeatherReport($city);

    if (!$status) {
        return $weatherService->printPage($request, $response, "Error - weather");
    }

    if($city) {
        $status = $weatherService->saveRecordToDatabase();
    }

    if (!$status) {
        return $weatherService->printPage($request, $response, "Error - base");
    }

    return $weatherService->printPage($request, $response, $errorContents);
});

return $app;