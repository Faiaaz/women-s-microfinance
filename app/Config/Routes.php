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
$routes->get('facebook/webhook', 'Chatbot::webhook');
$routes->post('facebook/webhook', 'Chatbot::receiveMessage');
$routes->get('test', 'Chatbot::test');
$routes->get('test-message', 'Chatbot::testMessage');
$routes->get('check-logs', 'Chatbot::checkLogs');