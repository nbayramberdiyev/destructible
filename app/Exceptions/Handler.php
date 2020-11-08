<?php

declare(strict_types=1);

namespace App\Exceptions;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Psr\Container\ContainerInterface as Container;
use Psr\Http\Message\ResponseFactoryInterface as ResponseFactory;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Exception\HttpNotFoundException;
use Throwable;

class Handler
{
    protected ResponseFactory $responseFactory;

    protected Container $container;

    /**
     * Handler constructor.
     *
     * @param ResponseFactory $responseFactory
     * @param Container $container
     */
    public function __construct(ResponseFactory $responseFactory, Container $container)
    {
        $this->responseFactory = $responseFactory;
        $this->container = $container;
    }

    /**
     * @param Request $request
     * @param Throwable $exception
     * @return ResponseInterface
     */
    public function __invoke(Request $request, Throwable $exception): ResponseInterface
    {
        if ($exception instanceof HttpNotFoundException || $exception instanceof ModelNotFoundException) {
            return $this->render('errors/404.twig', 404);
        }

        if ($exception instanceof ValidationException) {
            $this->container->get('flash')->addMessage('errors', $exception->errors());

            return $this->responseFactory
                ->createResponse(422)
                ->withHeader('Location', $exception->redirectTo ?? $request->getHeader('Referer'));
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
