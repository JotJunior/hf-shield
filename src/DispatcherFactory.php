<?php

namespace Jot\HfOAuth2;

use Hyperf\HttpServer\Router\DispatcherFactory as Dispatcher;
use Hyperf\HttpServer\Router\RouteCollector;
use Hyperf\HttpServer\Router\Router;

class DispatcherFactory extends Dispatcher
{

    public function initConfigRoute()
    {
        parent::initConfigRoute();

//        Router::addRoute(['POST'], '/oauth/token', '\Jot\HfOAuth2\Controller\AccessTokenController@issueToken');
//        Router::addGroup('/oauth', function (RouteCollector $router) {
//            $router->addRoute('DELETE', '/tokens', '\Jot\HfOAuth2\Controller\AccessTokenController@revokeToken');
//
//            $router->addRoute('POST', '/users', '\Jot\HfOAuth2\Controller\UserController@createUser');
//            $router->addRoute('PUT', '/users/{id}', '\Jot\HfOAuth2\Controller\UserController@updateUser');
//
//            $router->addRoute('POST', '/clients', '\Jot\HfOAuth2\Controller\UserController@createClient');
//
//        });

    }
}