<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Doctrine\DBAL\DriverManager;

use Symfony\Component\HttpFoundation\Request;

use Pvt\DataAccess\SqlUserStore;
use Pvt\DataAccess\SqlAccessTokenStore;
use Pvt\Exceptions\DuplicateUserException;
use Pvt\Interactors\CreateUser;
use Pvt\Interactors\AuthenticateUserWithAccessToken;
use Pvt\Interactors\AuthenticateUserWithPassword;

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

$userStore = new SqlUserStore($connection);
$createUser = new CreateUser($userStore);

$tokenStore = new SqlAccessTokenStore($connection);
$authenticateWithAccessToken = new AuthenticateUserWithAccessToken($userStore, $tokenStore);
$authenticateWithPassword = new AuthenticateUserWithPassword($userStore, $tokenStore);

$app = new \Silex\Application();
$app['debug'] = true;

$app->post('/report', function (Silex\Application $app, Request $request) use ($authenticateWithAccessToken) {
    $tokenString = $request->get('access_token');
    $result = $authenticateWithAccessToken->execute($tokenString);
    if (!$result->isOk()) {
        $response = errorResponse(401, 'Please supply a valid access token.');
        return $app->json($response, $response['error']['code']);
    }
    return $app->json('', 201);
});

$app->post('/users', function (Silex\Application $app, Request $request) use ($createUser, $authenticateWithPassword) {
    try {
        $result = $createUser->execute(
            $request->get('name'),
            $request->get('email'),
            $request->get('password')
        );
    }
    catch (DuplicateUserException $e) {
        $response = errorResponse(409, 'That email address has already been used to register an account.');
        return $app->json($response, $response['error']['code']);
    }
    if (!$result->isOk()) {
        $response = errorResponse(400, 'Please supply a valid email, password and name.');
        return $app->json($response, $response['error']['code']);
    }
    $result = $authenticateWithPassword->execute($request->get('email'), $request->get('password'));
    $accessToken = $result->accessToken();
    $user = $result->user();
    return $app->json(array(
        'access_token' => $accessToken->token(),
        'profile_url' => $user->profileUrl(),
    ));
});

$app->post('/login', function( Silex\Application $app, Request $request) use ($authenticateWithPassword) {
    $email = $request->get('email');
    $password = $request->get('password');
    $result = $authenticateWithPassword->execute($email, $password);
    if (!$result->isOk()) {
        $response = errorResponse(401, 'Invalid email address or password. Please try again.');
        return $app->json($response, $response['error']['code']);
    }
    $accessToken = $result->accessToken();
    $user = $result->user();
    return $app->json(array(
        'access_token' => $accessToken->token(),
        'profile_url' => $user->profileUrl(),
    ));
});

function errorResponse($code, $message)
{
    return array(
        'error' => array(
            'code' => $code,
            'message' => $message
        )
    );
}

$app->run();
