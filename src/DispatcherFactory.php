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

        Router::addRoute(['POST'], '/v1/oauth/token', '\Jot\HfOAuth2\Controller\AccessTokenController@issueToken');
        Router::addGroup('/v1/oauth', function (RouteCollector $router) {
            $router->addRoute('GET', '/tokens', '\Jot\HfOAuth2\Controller\AuthorizedAccessTokenController@forUser');
            $router->addRoute('DELETE', '/tokens/{token_id}', '\Jot\HfOAuth2\Controller\AuthorizedAccessTokenController@destroy');
        });
    }


}