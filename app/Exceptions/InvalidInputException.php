<?php

declare(strict_types=1);

namespace App\Exceptions;

use Exception;

class InvalidInputException extends Exception
{
    protected $message = 'Invalid input';
}