<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Exceptions\ValidationException;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\UploadedFileInterface as UploadedFile;
use Valitron\Validator;

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

    /**
     * Validate the given request with the given rules.
     *
     * @param Request $request
     * @param array $rules
     * @return array|object|null
     * @throws ValidationException
     */
    public function validate(Request $request, array $rules = [])
    {
        $validator = new Validator($params = $request->getParsedBody());

        $validator->mapFieldsRules($rules);

        if (!$validator->validate()) {
            throw new ValidationException($validator);
        }

        return $params;
    }

    /**
     * Validate the given file with the given rules.
     *
     * @param UploadedFile $uploadedFile
     * @param array $rules
     * @return UploadedFile
     * @throws ValidationException
     */
    public function validateFile(UploadedFile $uploadedFile, array $rules = [])
    {
        $validator = new Validator([
            'file' => [
                'type' => $uploadedFile->getClientMediaType(),
                'size' => $uploadedFile->getSize(),
            ]
        ]);

        $validator->mapFieldsRules($rules);

        if (!$validator->validate()) {
            throw new ValidationException($validator);
        }

        return $uploadedFile;
    }
}
