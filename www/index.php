<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Doctrine\DBAL\DriverManager;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Pvt\DataAccess\SqlUserStore;
use Pvt\DataAccess\SqlAccessTokenStore;
use Pvt\DataAccess\SqlPvtResultStore;
use Pvt\Interactors\CreateUser;
use Pvt\Interactors\CreateUserResult;
use Pvt\Interactors\AuthenticateUserWithAccessToken;
use Pvt\Interactors\AuthenticateUserWithPassword;
use Pvt\Interactors\SubmitPvtResult;
use Pvt\Interactors\SubmitPvtResultResult;

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
$pvtResultStore = new SqlPvtResultStore($connection);

$authenticateWithAccessToken = new AuthenticateUserWithAccessToken($userStore, $tokenStore);
$authenticateWithPassword = new AuthenticateUserWithPassword($userStore, $tokenStore);
$submitPvtResult = new SubmitPvtResult($pvtResultStore);

$app = new \Silex\Application();
$app['debug'] = true;

$app->post('/report', function (Silex\Application $app, Request $request) use ($authenticateWithAccessToken, $submitPvtResult) {
    $tokenString = $request->get('access_token');

    $result = $authenticateWithAccessToken->execute($tokenString);

    if (!$result->isOk()) {
        $response = errorResponse(401, 'Please supply a valid access token.');
        return $app->json($response, $response['error']['code']);
    }

    $userId = $result->user()->id();
    $timestamp = $request->get('timestamp');
    $errorCount = $request->get('errors');
    $responseTimes = explode(',', $request->get('response_times'));

    $result = $submitPvtResult->execute($userId, $timestamp, $errorCount, $responseTimes);

    $responseCode = 201;
    if ($result->hasError(SubmitPvtResultResult::DUPLICATE_SUBMISSION)) {
        $responseCode = 301;
    }
    return new Response($result->pvtResult()->reportUrl(), $responseCode);
});

$app->post('/users', function (Silex\Application $app, Request $request) use ($createUser, $authenticateWithPassword) {
    $name = $request->get('name');
    $email = $request->get('email');
    $password = $request->get('password');

    $result = $createUser->execute($name, $email, $password);

    if ($result->hasError(CreateUserResult::DUPLICATE_USER)) {
        $response = errorResponse(409, 'That email address has already been used to register an account.');
        return $app->json($response, $response['error']['code']);
    }

    if (!$result->isOk()) {
        $response = errorResponse(400, 'Please supply a valid email, password and name.');
        return $app->json($response, $response['error']['code']);
    }

    $result = $authenticateWithPassword->execute($email, $password);

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
