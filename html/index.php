<?php

require_once __DIR__ . '/../vendor/autoload.php';

$app = new \Silex\Application([
    'debug' => true
]);
//
$app->register(new \Silex\Provider\SessionServiceProvider());
//$app->register(new \Silex\Provider\SecurityServiceProvider());

$app->register(new \cwreden\requestLimiter\RequestLimiterServiceProvider());

$app->get('/', function () {
    return 'index';
});

$app->get('/api/', function () {
    return 'api';
});

$app->get('/api/customers', function () {
    return 'customers';
});

$app->get('/api/orders', function () {
    return 'orders';
});

$app->run();
