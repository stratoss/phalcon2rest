<?php
namespace Phalcon2Rest\Modules\V1\Controllers;

use Phalcon\Di,
    Phalcon\Di\Injectable;
/**
 *  \Phalcon\Mvc\Controller has a final __construct() method, so we can't
 *  extend the constructor (which we will need for our RESTController).
 *  Thus we extend DI\Injectable instead.
 */
class BaseController extends Injectable {

    public function __construct(){
        $di = Di::getDefault();
        $this->setDI($di);
    }

}