<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

$routes->setDefaultController('Home');
$routes->get('/', 'Home::index');

// Chatbot routes
$routes->get('chatbot', 'Chatbot::index');
$routes->post('chatbot/processMessage', 'Chatbot::processMessage');

// Facebook Messenger webhook routes
$routes->get('webhook', 'Chatbot::webhook');
$routes->post('webhook', 'Chatbot::receiveMessage');
$routes->get('test', 'Chatbot::test');