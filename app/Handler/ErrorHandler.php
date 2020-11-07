<?php

declare(strict_types=1);

namespace App\Handler;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Exception\HttpNotFoundException;
use Throwable;

class ErrorHandler
{
    protected ResponseFactoryInterface $responseFactory;

    protected ContainerInterface $container;

    /**
     * ErrorHandler constructor.
     *
     * @param ResponseFactoryInterface $responseFactory
     * @param ContainerInterface $container
     */
    public function __construct(ResponseFactoryInterface $responseFactory, ContainerInterface $container)
    {
        $this->responseFactory = $responseFactory;
        $this->container = $container;
    }

    /**
     * Invoke.
     *
     * @param ServerRequestInterface $request
     * @param Throwable $exception
     * @param bool $displayErrorDetails
     * @param bool $logErrors
     * @param bool $logErrorDetails
     * @return ResponseInterface
     */
    public function __invoke(
        ServerRequestInterface $request,
        Throwable $exception,
        bool $displayErrorDetails,
        bool $logErrors,
        bool $logErrorDetails
    ): ResponseInterface {
        if ($exception instanceof HttpNotFoundException || $exception instanceof ModelNotFoundException) {
            return $this->render('errors/404.twig', 404);
        }

        return $this->render('errors/default.twig', $exception->getCode() ?: 500);
    }

    protected function render(string $view, int $statusCode = 500): ResponseInterface
    {
        return $this->container->get('view')->render(
            $this->responseFactory->createResponse($statusCode),
            $view
        );
    }
}