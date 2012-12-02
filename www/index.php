<?php

require_once __DIR__ . '/../vendor/autoload.php';

$app = new \Slim\Slim();

$app->post('/report', function() use ($app) {
    $app->response()->status(403);
});

$app->run();
