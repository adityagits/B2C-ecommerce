<?php
/** @var Router $router */

$router->get('/', 'HomeController@index');

$router->get('/products', 'ProductController@index');
$router->get('/products/{id}', 'ProductController@show');
$router->post('/products/{id}/review', 'ProductController@review');

$router->get('/cart', 'CartController@index');
$router->post('/cart/add', 'CartController@add');
$router->post('/cart/update', 'CartController@update');
$router->post('/cart/remove', 'CartController@remove');

$router->get('/login', 'AuthController@loginForm');
$router->post('/login', 'AuthController@login');
$router->get('/register', 'AuthController@registerForm');
$router->post('/register', 'AuthController@register');
$router->post('/logout', 'AuthController@logout');

$router->get('/checkout', 'CheckoutController@index');
$router->post('/checkout', 'CheckoutController@place');

$router->get('/orders', 'OrderController@index');
$router->get('/orders/{id}', 'OrderController@show');
$router->post('/orders/{id}/cancel', 'OrderController@cancel');

// Admin
$router->get('/admin', 'AdminController@dashboard');
$router->get('/admin/products', 'AdminProductController@index');
$router->get('/admin/products/create', 'AdminProductController@create');
$router->post('/admin/products', 'AdminProductController@store');
$router->get('/admin/products/{id}/edit', 'AdminProductController@edit');
$router->post('/admin/products/{id}', 'AdminProductController@update');
$router->post('/admin/products/{id}/delete', 'AdminProductController@destroy');
$router->get('/admin/orders', 'AdminOrderController@index');
$router->get('/admin/orders/{id}', 'AdminOrderController@show');
$router->post('/admin/orders/{id}/status', 'AdminOrderController@updateStatus');
