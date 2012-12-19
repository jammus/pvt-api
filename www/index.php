<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Doctrine\DBAL\DriverManager;

use Symfony\Component\HttpFoundation\Request;

use Pvt\DataAccess\SqlUserStore;
use Pvt\Exceptions\DuplicateUserException;
use Pvt\Interactors\CreateUser;

$connection = DriverManager::getConnection(
    array(
        'dbname' => 'pvt-test',
        'user' => 'jamess',
        'password' => '',
        'host' => '127.0.0.1',
        'driver' => 'pdo_pgsql',
    ),
    new \Doctrine\DBAL\Configuration()
);

$userstore = new SqlUserStore($connection);
$createUser = new CreateUser($userstore);

$app = new \Silex\Application();

$app['debug'] = true;

$app->post('/report', function (Silex\Application $app) {
    $app->response()->status(401);
});

$app->post('/users', function (Silex\Application $app, Request $request) use ($createUser) {
    try {
        $result = $createUser->create(
            $request->get('name'),
            $request->get('email'),
            $request->get('password')
        );
    }
    catch (DuplicateUserException $e) {
        $error = array(
            'error' => array(
                'code' => 409,
                'message' => 'That email address has already been used to register an account.'
            )
        );
        return $app->json($error, 409);
    }
    if (! $result->isOk()) {
        $error = array(
            'error' => array(
                'code' => 400,
                'message' => 'Please supply a valid email, password and name.'
            )
        );
        return $app->json($error, 400);
    }
    return $app->json(array());
});

$app->run();
