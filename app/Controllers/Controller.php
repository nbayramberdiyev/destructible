<?php

declare(strict_types=1);

namespace App\Controllers;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface as Response;

abstract class Controller
{
    protected ContainerInterface $container;

    /**
     * Set up controllers to have access to the container.
     *
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * Render the specified view.
     *
     * @param Response $response
     * @param string $view
     * @param array $data
     * @return Response
     */
    protected function render(Response $response, string $view, array $data = []): Response
    {
        return $this->container->get('view')->render($response, $view, $data);
    }

    /**
     * Flash the specified message with the key.
     *
     * @param string $key
     * @param string $message
     * @return mixed
     */
    protected function flash(string $key, string $message)
    {
        return $this->container->get('flash')->addMessage($key, $message);
    }
}
