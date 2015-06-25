<?php
use Zend\Stratigility\MiddlewarePipe;
use Zend\Diactoros\Server;
use Middleware\RouteManager;

require_once __DIR__ . '/../vendor/autoload.php';

$app    = new MiddlewarePipe();
$app->pipe(new RouteManager(require_once __DIR__ . '/../config/routes.php'));

$server = Server::createServer($app,$_SERVER,$_GET,$_POST,$_COOKIE,$_FILES);
$server->listen();