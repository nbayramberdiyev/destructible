<?php

declare(strict_types=1);

use App\Exceptions\Handler;
use Slim\Views\TwigMiddleware;
use Zeuxisoo\Whoops\Slim\WhoopsMiddleware;

return function (Slim\App $app) {
    $app->addRoutingMiddleware();

    $app->add(TwigMiddleware::createFromContainer($app));

    $app->add('csrf');

    if (env('APP_DEBUG')) {
        $app->add(new WhoopsMiddleware());
    } else {
        $app
            ->addErrorMiddleware(false, true, true)
            ->setDefaultErrorHandler(new Handler($app->getResponseFactory(), $app->getContainer()));
    }
};
