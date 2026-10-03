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
$router->get('/orders/{id}/invoice', 'OrderController@invoice');
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
$router->post('/admin/orders/{id}/payment', 'AdminOrderController@updatePayment');
$router->post('/admin/orders/{id}/shipment', 'AdminOrderController@updateShipment');
$router->post('/admin/orders/{id}/cancel', 'AdminOrderController@cancel');
$router->get('/admin/invoices', 'AdminBillingController@invoices');
$router->get('/admin/payments', 'AdminBillingController@payments');

// Admin: key management
$router->get('/admin/keys', 'AdminKeyController@index');
$router->post('/admin/keys/api', 'AdminKeyController@createApiKey');
$router->post('/admin/keys/api/{id}/revoke', 'AdminKeyController@revokeApiKey');
$router->post('/admin/keys/credentials', 'AdminKeyController@saveCredential');
$router->post('/admin/keys/credentials/{id}/delete', 'AdminKeyController@deleteCredential');

// JSON API (Bearer API key)
$router->get('/api/products', 'ApiController@products');
$router->get('/api/products/{id}', 'ApiController@product');
$router->get('/api/orders', 'ApiController@orders');
$router->get('/api/orders/{id}', 'ApiController@order');
