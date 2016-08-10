<?php

use Phalcon\Di\FactoryDefault as DefaultDI,
    Phalcon\Loader;


require '../autoload.php';
require '../services.php';

//die(var_dump($di->getShared('authorizationServer')));
/**
 * Out application is a Micro application, so we mush explicitly define all the routes.
 * For APIs, this is ideal.  This is as opposed to the more robust MVC Application
 * @var $app
 */
$app = new Phalcon\Mvc\Micro();
$app->setDI($di);


/**
 * Mount all of the collections, which makes the routes active.
 */
foreach($di->get('collections') as $collection){
    $app->mount($collection);
}

/**
 * The base route return the list of defined routes for the application.
 * This is not strictly REST compliant, but it helps to base API documentation off of.
 * By calling this, you can quickly see a list of all routes and their methods.
 */
$app->get('/', function() use ($app){
    $routes = $app->getRouter()->getRoutes();
    $routeDefinitions = [
        'GET' => [],
        'POST' => [],
        'PUT' => [],
        'PATCH' => [],
        'DELETE' => [],
        'HEAD' => [],
        'OPTIONS' => []
    ];
    /* @var $route Phalcon\Mvc\Router\Route */
    foreach($routes as $route){
        $method = $route->getHttpMethods();
        $routeDefinitions[$method][] = $route->getPattern();
    }
    return $routeDefinitions;
});

/**
 * Before every request, make sure user is authenticated.
 * Returning true in this function resumes normal routing.
 * Returning false stops any route from executing.
 */

$app->before(function () use ($app, $di) {
    $config = $app->config;
    // getting access token is permitted ;)
    if (strpos($app->request->getURI(), '/access_token') !== FALSE ||
        strpos($app->request->getURI(), '/authorize') !== FALSE ||
        $app->request->isOptions()
    ) {
        return $di->getShared('rateLimits', ['access_token', $app->request->getClientAddress(), $app]);
    }

    $accessTokenRepository = new \Phalcon2Rest\Components\Oauth2\Repositories\AccessTokenRepository(); // instance of AccessTokenRepositoryInterface
    $publicKeyPath = 'file://' . __DIR__ . '/../' . $config->oauth['public'];
    try {
        $server = new \League\OAuth2\Server\ResourceServer(
            $accessTokenRepository,
            $publicKeyPath
        );

        $auth = new \League\OAuth2\Server\Middleware\ResourceServerMiddleware($server);
        $auth(new \Phalcon2Rest\Components\Oauth2\Request($app->request), new \Phalcon2Rest\Components\Oauth2\Response($app->response), function(){});
        if (isset($_SERVER['oauth_access_token_id']) &&
            isset($_SERVER['oauth_client_id']) &&
            isset($_SERVER['oauth_user_id']) &&
            isset($_SERVER['oauth_scopes'])
        ) {
            // TODO: save somewhere the user_id and scopes for future validations, e.g. /users/1/edit
            // TODO: should be accessible only if the user_id is 1 or the scope is giving permissions, e.g. admin
            if (strlen($_SERVER['oauth_client_id']) > 0) {
                return $di->getShared('rateLimits', ['api_common', 'client'.$_SERVER['oauth_client_id'], $app]);
            } else {
                return $di->getShared('rateLimits', ['api_common', 'user'.$_SERVER['oauth_user_id'], $app]);
            }

        }
    } catch (\League\OAuth2\Server\Exception\OAuthServerException $e) {
    }
    $rateLimit = $di->getShared('rateLimits', ['api_unauthorized', $app->request->getClientAddress(), $app]);
    if ($rateLimit === false) {
        return false;
    }
    throw new \Phalcon2Rest\Exceptions\HttpException(
        'Unauthorized',
        401,
        false,
        [
            'dev' => 'The bearer token is missing or is invalid',
            'internalCode' => 'P1008',
            'more' => ''
        ]
    );
});

/**
 * After a route is run, usually when its Controller returns a final value,
 * the application runs the following function which actually sends the response to the client.
 *
 * The default behavior is to send the Controller's returned value to the client as JSON.
 * However, by parsing the request querystring's 'type' paramter, it is easy to install
 * different response type handlers.  Below is an alternate csv handler.
 *
 * TODO: add versions
 */
$app->after(function() use ($app) {

    // OPTIONS have no body, send the headers, exit
    if($app->request->getMethod() == 'OPTIONS'){
        $app->response->setStatusCode('200', 'OK');
        $app->response->send();
        return;
    }

    // Respond by default as JSON
    if(!$app->request->get('type') || $app->request->get('type') == 'json'){

        // Results returned from the route's controller.  All Controllers should return an array
        $records = $app->getReturnedValue();
        $response = new \Phalcon2Rest\Responses\JsonResponse();
        $response->useEnvelope(false)
            ->convertSnakeCase(true)
            ->send($records);

        return;
    }
    elseif($app->request->get('type') == 'csv'){

        $records = $app->getReturnedValue();
        $response = new \Phalcon2Rest\Responses\CsvResponse();
        $response->useHeaderRow(true)->send($records);

        return;
    }
    else {
        throw new \Phalcon2Rest\Exceptions\HttpException(
            'Could not return results in specified format',
            403,
            false,
            array(
                'dev' => 'Could not understand type specified by type parameter in query string.',
                'internalCode' => 'NF1000',
                'more' => 'Type may not be implemented. Choose either "csv" or "json"'
            )
        );
    }
});

/**
 * The notFound service is the default handler function that runs when no route was matched.
 * We set a 404 here unless there's a suppress error codes.
 */
$app->notFound(function () use ($app) {
    throw new \Phalcon2Rest\Exceptions\HttpException(
        'Not Found.',
        404,
        false,
        array(
            'dev' => 'That route was not found on the server.',
            'internalCode' => 'NF1000',
            'more' => 'Check route for misspellings.'
        )
    );
});

/**
 * If the application throws an HttpException, send it on to the client as json.
 * Elsewise, just log it.
 * TODO:  Improve this.
 */
set_exception_handler(function($exception) use ($app){
    //HttpException's send method provides the correct response headers and body
    /* @var $exception Phalcon2Rest\Exceptions\HttpException */
    if(is_a($exception, 'Phalcon2Rest\\Exceptions\\HttpException')){
        $exception->send();
    }
    //error_log($exception);
    //error_log($exception->getTraceAsString());
});

$app->handle();
