<?php
// Include the Router class
require_once 'Router.php';

// Create a new Router instance
$router = new Router();

// Route for /Home
$router->get('/Home', function() {
    // Include home.php
    require_once 'home.php';
});

// Set a 404 handler (optional)
$router->setNotFoundHandler(function() {
    http_response_code(404);
    echo '404 Page Not Found';
});

// Run the router
$router->run();