<?php

declare(strict_types=1);

// Start a session
session_start();

// Load env variables
(Dotenv\Dotenv::createImmutable(dirname(__DIR__)))->load();

// Create container instance
$container = require __DIR__ . '/container.php';

// Create app instance
$app = $container->get(Slim\App::class);

// Register routes
(require __DIR__ . '/../routes/web.php')($app);

// Register global middleware
(require __DIR__ . '/middleware.php')($app);

return $app;
