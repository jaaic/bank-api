<?php

/**  @var $router \Laravel\Lumen\Routing\Router */
$router->get('/', function () {
});

$router->post('/transfer', ['uses' => 'TransferController@transfer']);