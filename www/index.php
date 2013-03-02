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
use Pvt\Security\OAuth2TokenStorage;

$services = json_decode(getenv('VCAP_SERVICES'), true);
$dbConfig = $services ? $services['postgresql-9.1'][0]['credentials'] : array(
    'username' => 'jamess',
    'password' => '',
    'hostname' => '127.0.01',
    'port' => '',
    'name' => 'pvt-test',
);

$connection = DriverManager::getConnection(
    array(
        'dbname' => $dbConfig['name'],
        'user' => $dbConfig['username'],
        'password' => $dbConfig['password'],
        'host' => $dbConfig['hostname'],
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

$oAuth2Storage = new OAuth2TokenStorage($userStore, $tokenStore);
$oAuth2Server = new OAuth2\OAuth2($oAuth2Storage, array());

$app = new \Silex\Application();
$app['debug'] = true;

$app->post('/report', function (Silex\Application $app, Request $request) use ($oAuth2Server, $authenticateWithAccessToken, $submitPvtResult) {
    $tokenString = $oAuth2Server->getBearerToken($request);

    $result = $authenticateWithAccessToken->execute($tokenString);

    if ( ! $result->isOk()) {
        return notAuthorised($app);
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
    return $app->json(
        array(
            'code' => $responseCode,
            'response' => array(
                'location' => $result->pvtResult()->reportUrl()
            ),
        ),
        $responseCode,
        array(
            'Location' => $result->pvtResult()->reportUrl()
        )
    );
});

$app->post('/users', function (Silex\Application $app, Request $request) use ($createUser, $authenticateWithPassword) {
    $name = $request->get('name');
    $email = $request->get('email');
    $password = $request->get('password');

    $result = $createUser->execute($name, $email, $password);

    if ($result->hasError(CreateUserResult::DUPLICATE_USER)) {
        $response = errorResponse(409, 'duplicate_user', 'That email address has already been used to register an account.');
        return $app->json($response, $response['code']);
    }

    if ( ! $result->isOk()) {
        $response = errorResponse(400, 'invalid_request', 'Please supply a valid email, password and name.');
        return $app->json($response, $response['code']);
    }

    $result = $authenticateWithPassword->execute($email, $password);

    $accessToken = $result->accessToken();
    $user = $result->user();

    return $app->json(
        array(
            'code' => 200,
            'access_token' => $accessToken->token(),
            'user' => array(
                'id' => $user->id(),
                'name' => $user->name(),
                'email' => $user->email(),
                'profile_url' => $user->profileUrl(),
            ),
        )
    );
});

$app->get('/users/{userId}/report/{timestamp}', function (Silex\Application $app, Request $request, $userId, $timestamp) use ($pvtResultStore, $oAuth2Server, $authenticateWithAccessToken) {
    $tokenString = $oAuth2Server->getBearerToken($request);

    $result = $authenticateWithAccessToken->execute($tokenString);

    if ( ! $result->isOk() || $result->user()->id() != $userId) {
        return notAuthorised($app);
    }

    $pvtResult = $pvtResultStore->fetchByUserIdAndTimestamp($userId, $timestamp);

    return $app->json(
        array(
            'code' => 200,
            'response' => array(
                'report' => array(
                    'timestamp' => $pvtResult->date()->getTimestamp(),
                    'errors' => $pvtResult->errors(),
                    'lapses' => $pvtResult->lapses(),
                    'average_response_time' => $pvtResult->averageResponseTime(),
                ),
            ),
        )
    );
});

$app->post('/token', function (Silex\Application $app, Request $request) use ($oAuth2Server, $userStore, $tokenStore) {
    try {
        $response = $oAuth2Server->grantAccessToken($request);
        $content = json_decode($response->getContent(), true);
        $token = $content['access_token'];
        $accessToken = $tokenStore->fetchByTokenString($token);
        $user = $userStore->fetchById($accessToken->userId());
        $content['user'] = array(
            'id' => $user->id(),
            'name' => $user->name(),
            'email' => $user->email(),
            'profile_url' => $user->profileUrl()
        );
        $response->setContent(json_encode($content));
        return $response;
    }
    catch (\OAuth2\OAuth2ServerException $e) {
        if ($e->getMessage() === 'invalid_grant') {
            $response = errorResponse(400, $e->getMessage(), 'Invalid email address or password. Please try again.');
            return $app->json($response, $response['code']);
        }
        $response = errorResponse(400, $e->getMessage(), $e->getDescription());
        return $app->json($response, $response['code']);
    }
    catch (Exception $e) {
        $response = errorResponse(500, null, 'Unexpected error');
        return $app->json($response, $response['code']);
    }
});

function errorResponse($code, $message = null, $description = null)
{
    $response = array('code' => $code);

    if ($message !== null) {
        $response['error'] = $message;
    }

    if ($description !== null) {
        $response['error_description'] = $description;
    }

    return $response;
}

function notAuthorised($app)
{
    $response = errorResponse(401, 'access_denied', 'Your request could not be authorised.');
    return $app->json(
        $response,
        $response['code'],
        array(
            'WWW-Authenticate' => 'Bearer realm="pvt"'
        )
    );
}

$app->run();
