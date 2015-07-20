<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$app = require_once __DIR__ . '/../app/bootstrap.php';

use Silex\Provider\TwigServiceProvider;
use Silex\Provider\SessionServiceProvider;
use Silex\Provider\UrlGeneratorServiceProvider;

//Debug
$app['debug'] = true;

$app
    ->register(new TwigServiceProvider(), array(
        'twig.path' => __DIR__.'/../views'
    ))
    ->register(new SessionServiceProvider())
    ->register(new UrlGeneratorServiceProvider());

$app->before(function ($request) {
    $request->getSession()->start();
});

include(__DIR__ . '/../app/config/routes.php');

$app->run();