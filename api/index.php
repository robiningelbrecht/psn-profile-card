<?php

use App\Cache;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Factory\AppFactory;

require __DIR__ . '/../vendor/autoload.php';

$app = AppFactory::create();

$app->get('/', function (ServerRequestInterface $request, ResponseInterface $response) {
    $response->getBody()->write(Cache::forSvg()->get());
    return $response->withHeader('Content-Type', 'image/svg+xml');
});
$app->get('/debug', function (ServerRequestInterface $request, ResponseInterface $response) {
    $response->getBody()->write(Cache::forDebug()->get());
    return $response;
});

$app->run();