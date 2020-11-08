<?php

declare(strict_types=1);

use App\Views\CsrfExtension;
use DI\ContainerBuilder;
use Psr\Container\ContainerInterface;
use Slim\App;
use Slim\Csrf\Guard;
use Slim\Factory\AppFactory;
use Slim\Flash\Messages;
use Slim\Views\Twig;
use Twig\Extension\DebugExtension;

$containerBuilder = new ContainerBuilder();

$containerBuilder->addDefinitions([
    App::class => function (ContainerInterface $container) {
        AppFactory::setContainer($container);
        return AppFactory::create();
    },

    'config' => fn () => [
        'app' => [
            'name'  => env('APP_NAME'),
            'debug' => env('APP_DEBUG'),
        ],
        'mail' => [
            'host'        => env('MAIL_HOST'),
            'port'        => env('MAIL_PORT'),
            'username'    => env('MAIL_USERNAME'),
            'password'    => env('MAIL_PASSWORD'),
            'fromAddress' => env('MAIL_FROM_ADDRESS'),
            'fromName'    => env('MAIL_FROM_NAME'),
        ],
    ],

    'flash' => fn () => new Messages(),

    'csrf' => fn (ContainerInterface $container) => new Guard($container->get(App::class)->getResponseFactory()),

    'view' => function (ContainerInterface $container) {
        $twig =Twig::create(__DIR__ . '/../resources/views', [
            'cache' => false,
            'debug' => $container->get('config')['app']['debug'],
        ]);

        $twig->addExtension(new CsrfExtension($container->get('csrf')));
        $twig->addExtension(new DebugExtension());

        $twig->getEnvironment()->addGlobal('messages', $container->get('flash')->getMessages());
        $twig->getEnvironment()->addGlobal('errors', $container->get('flash')->getFirstMessage('errors'));
        $twig->getEnvironment()->addGlobal('app_name', $container->get('config')['app']['name']);

        return $twig;
    },

    'db' => require __DIR__ . '/database.php',

    'mailer' => function (ContainerInterface $container) {
        $mail = $container->get('config')['mail'];

        $transport = (new Swift_SmtpTransport($mail['host'], $mail['port']))
            ->setUsername($mail['username'])
            ->setPassword($mail['password']);

        return new Swift_Mailer($transport);
    },
]);

return $containerBuilder->build();
