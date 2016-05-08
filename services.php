<?php

use Phalcon\Di\FactoryDefault as DefaultDI,
    Phalcon\Config\Adapter\Ini as IniConfig,
    League\OAuth2\Server\ResourceServer,
    Phalcon2Rest\Components\Oauth2\Repositories\AccessTokenRepository,
    League\OAuth2\Server\AuthorizationServer,
    Phalcon2Rest\Components\Oauth2\Repositories\ClientRepository,
    Phalcon2Rest\Components\Oauth2\Repositories\ScopeRepository,
    League\OAuth2\Server\Grant\PasswordGrant,
    Phalcon2Rest\Components\Oauth2\Repositories\UserRepository,
    Phalcon2Rest\Components\Oauth2\Repositories\RefreshTokenRepository,
    League\OAuth2\Server\Grant\ClientCredentialsGrant,
    League\OAuth2\Server\Grant\RefreshTokenGrant;

/**
 * The DI is our direct injector.  It will store pointers to all of our services
 * and we will insert it into all of our controllers.
 * @var DefaultDI
 */
$di = new DefaultDI();

/**
 * $di's setShared method provides a singleton instance.
 * If the second parameter is a function, then the service is lazy-loaded
 * on its first instantiation.
 */
$di->setShared('config', function() {
    return new IniConfig(__DIR__ . "/config/config.ini");
});

/**
 * Return array of the Collections, which define a group of routes, from
 * routes/collections.  These will be mounted into the app itself later.
 */
$availableVersions = $di->getShared('config')->versions;

$allCollections = [];
foreach ($availableVersions as $versionString => $versionPath) {
    $currentCollections = include('Modules/' . $versionPath . '/Routes/routeLoader.php');
    $allCollections = array_merge($allCollections, $currentCollections);
}
$di->set('collections', function() use ($allCollections) {
    return $allCollections;
});

// As soon as we request the session service, it will be started.
$di->setShared('session', function() {
    $session = new \Phalcon\Session\Adapter\Files();
    $session->start();
    return $session;
});

/**
 * The slowest option! Consider using memcached/redis or another faster caching system than file...
 * Using the file cache just for the sake of the simplicity here
 */
$di->setShared('cache', function() {
    //Cache data for one day by default
    $frontCache = new \Phalcon\Cache\Frontend\Data(array(
        'lifetime' => 3600
    ));

    //File cache settings
    $cache = new \Phalcon\Cache\Backend\File($frontCache, array(
        'cacheDir' => __DIR__ . '/cache/'
    ));

    return $cache;
});

$di->setShared('rateLimits', function($limitType, $identifier, $app) use ($di) {
    $cache = $di->getShared('cache');
    $config = $di->getShared('config');
    $limitName = $limitType . '_limits';
    if (property_exists($config, $limitName)) {
        foreach ($config->{$limitName} as $limit => $seconds) {
            $limit = substr($limit, 1, strlen($limit));
            $cacheName = $limitName . $identifier;

            if ($cache->exists($cacheName, $seconds)) {
                $rate = $cache->get($cacheName, $seconds);
                $rate['remaining']--;
                $resetAfter = $rate['saved'] + $seconds - time();
                if ($rate['remaining'] > -1) {
                    $cache->save($cacheName, $rate, $resetAfter);
                }
            } else {
                $rate = ['remaining' => $limit - 1, 'saved' => time()];
                $cache->save($cacheName, $rate, $seconds);
                $resetAfter = $seconds;
            }

            $app->response->setHeader('X-Rate-Limit-Limit', $limit);
            $app->response->setHeader('X-Rate-Limit-Remaining', ($rate['remaining'] > -1 ? $rate['remaining'] : 0) . ' ');
            $app->response->setHeader('X-Rate-Limit-Reset', $resetAfter . ' ');

            if ($rate['remaining'] > -1) {
                return true;
            } else {
                throw new \Phalcon2Rest\Exceptions\HttpException(
                    'Too Many Requests',
                    429,
                    null,
                    [
                        'dev' => 'You have reached your limit. Please try again after ' . $resetAfter . ' seconds.',
                        'internalCode' => 'P1010',
                        'more' => ''
                    ]
                );
            }
        }
    }
    return false;
});

/**
 * Database setup.  Here, we'll use a simple SQLite database of Disney Princesses.
 */
$di->set('db', function() {
    return new \Phalcon\Db\Adapter\Pdo\Sqlite(array(
        'dbname' => __DIR__ . '/data/database.db'
    ));
});

/**
 * If our request contains a body, it has to be valid JSON.  This parses the
 * body into a standard Object and makes that available from the DI.  If this service
 * is called from a function, and the request body is nto valid JSON or is empty,
 * the program will throw an Exception.
 */
$di->setShared('requestBody', function() {
    $in = file_get_contents('php://input');
    $in = json_decode($in, FALSE);

    // JSON body could not be parsed, throw exception
    if($in === null){
        throw new HttpException(
            'There was a problem understanding the data sent to the server by the application.',
            409,
            array(
                'dev' => 'The JSON body sent to the server was unable to be parsed.',
                'internalCode' => 'REQ1000',
                'more' => ''
            )
        );
    }

    return $in;
});

$di->setShared('resourceServer', function() use ($di) {
    $config = $di->getShared('config');
    $server = new ResourceServer(
        new AccessTokenRepository(),            // instance of AccessTokenRepositoryInterface
        'file://' . __DIR__ . '/' . $config->oauth['public']  // the authorization server's public key
    );
    return $server;
});

$di->set('security', function () {

    $security = new \Phalcon\Security();

    // Set the password hashing factor to 12 rounds
    $security->setWorkFactor(12);

    return $security;
}, true);

$di->setShared('authorizationServer', function() use ($di) {
    $config = $di->getShared('config');
    $server = new AuthorizationServer(
        new ClientRepository(),                 // instance of ClientRepositoryInterface
        new AccessTokenRepository(),            // instance of AccessTokenRepositoryInterface
        new ScopeRepository(),                  // instance of ScopeRepositoryInterface
        'file://' . __DIR__ . '/' . $config->oauth['private'],    // path to private key
        'file://' . __DIR__ . '/' . $config->oauth['public']      // path to public key
    );

    /**
     * Using client_id & client_secret & username & password
     *
     */
    $passwordGrant = new PasswordGrant(
        new UserRepository(),           // instance of UserRepositoryInterface
        new RefreshTokenRepository()    // instance of RefreshTokenRepositoryInterface
    );
    $passwordGrant->setRefreshTokenTTL(new \DateInterval($config->oauth['refreshTokenLifetime']));
    $server->enableGrantType(
        $passwordGrant,
        new \DateInterval($config->oauth['accessTokenLifetime'])
    );

    /**
     * Using client_id & client_secret
     */
    $clientCredentialsGrant = new ClientCredentialsGrant();
    $server->enableGrantType(
        $clientCredentialsGrant,
        new \DateInterval($config->oauth['accessTokenLifetime'])
    );

    $refreshTokenGrant = new RefreshTokenGrant(new RefreshTokenRepository());
    $refreshTokenGrant->setRefreshTokenTTL(new DateInterval($config->oauth['refreshTokenLifetime']));
    $server->enableGrantType($refreshTokenGrant, new DateInterval($config->oauth['accessTokenLifetime']));
    return $server;
});