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

        Router::addRoute(['POST'], '/oauth/token', '\Jot\HfOAuth2\Controller\AccessTokenController@issueToken');
        Router::addGroup('/oauth', function (RouteCollector $router) {
            $router->addRoute('GET', '/tokens', '\Jot\HfOAuth2\Controller\AccessTokenController@forUser');
            $router->addRoute('DELETE', '/tokens/{token_id}', '\Jot\HfOAuth2\Controller\AccessTokenController@destroy');
        });
    }
}