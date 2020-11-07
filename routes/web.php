<?php

declare(strict_types=1);

use App\Controllers\HomeController;
use App\Controllers\MessageController;

return function (Slim\App $app) {
    $app->get('/', HomeController::class . ':index')->setName('home.index');
    $app->post('/messages', MessageController::class . ':store')->setName('messages.store');
    $app->get('/messages/{uuid}', MessageController::class . ':show')->setName('messages.show');
};
