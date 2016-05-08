<?php

/**
 * Collections let us define groups of routes that will all use the same controller.
 * We can also set the handler to be lazy loaded.  Collections can share a common prefix.
 */

return call_user_func(function() {

    $exampleCollection = new \Phalcon\Mvc\Micro\Collection();

    $exampleCollection
        // VERSION NUMBER SHOULD BE FIRST URL PARAMETER, ALWAYS
        ->setPrefix('/v1/access_token')
        // Must be a string in order to support lazy loading
        ->setHandler('\Phalcon2Rest\Modules\V1\Controllers\AccessTokenController')
        ->setLazy(true);

    // Set Access-Control-Allow headers.
    $exampleCollection->options('/', 'optionsBase');

    $exampleCollection->post('/', 'post');

    return $exampleCollection;
});