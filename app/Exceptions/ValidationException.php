<?php

declare(strict_types=1);

namespace App\Exceptions;

use Exception;
use Valitron\Validator;

class ValidationException extends Exception
{
    public Validator $validator;

    public string $redirectTo;

    /**
     * ValidationException constructor.
     *
     * @param Validator $validator
     * @param string|null $redirectTo
     */
    public function __construct(Validator $validator, ?string $redirectTo = null)
    {
        parent::__construct('The given data was invalid.', 422);

        $this->validator = $validator;
        $this->redirectTo = $redirectTo ?? $_SERVER['HTTP_REFERER'];
    }

    /**
     * Get all of the validation error messages.
     *
     * @return array|bool
     */
    public function errors()
    {
        return $this->validator->errors();
    }
}
