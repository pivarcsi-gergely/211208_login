<?php
use Slim\Factory\AppFactory;

require __DIR__ . '/../vendor/autoload.php';

$app = AppFactory::create();
$routes = require '../src/routes.php';
$routes($app);

$app->run();