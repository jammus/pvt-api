<?php

require_once __DIR__ . '/../vendor/autoload.php';

$app = new \Slim\Slim();

$app->get('/', function() {
    echo "It Works!";
});

$app->run();
