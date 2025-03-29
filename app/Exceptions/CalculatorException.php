<?php

declare(strict_types=1);


namespace App\Exceptions;

use Exception;

class CalculatorException extends Exception
{
    public static function invalidMathFunction(): static
    {
        return new static('Invalid math function');
    }

    public static function invalidOperatorFunction(): static
    {
        return new static('Invalid operator function');
    }
}