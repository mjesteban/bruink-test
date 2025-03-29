<?php

declare(strict_types=1);

namespace App\Models;

use App\Exceptions\CalculatorException;
use App\Exceptions\InvalidInputException;
use Exception;
use Throwable;

readonly class Calculator
{
    public function __construct(private array $operators, private array $mathFunctions)
    {
    }

    public function run(): void
    {
        $this->welcomeMessage();

        while (true) {
            $prompt = 'calc > ';
            $line = (string) readline($prompt);

            if ($line === 'exit') {
                echo 'Bye!'.PHP_EOL;
                break;
            }

            try {
                $line = preg_replace('/[\s,]/', '', $line);

                $this->validateOperation($line);

                // Convert math functions e.g. "9sqrt" to sqrt(9);
                $line = $this->convertMathFunction($line, $this->getMathFunctions());

            } catch (CalculatorException|InvalidInputException|Exception $e) {
                echo $e->getMessage().PHP_EOL;
                continue;
            }

            echo $this->calculate($line).PHP_EOL;
        }
    }

    private function welcomeMessage(): void
    {
        $operators = implode(', ', $this->getOperators());
        $mathFunctions = implode(', ', $this->getMathFunctions());

        echo <<<HEREDOC
Welcome to Buink Calculator!

Arithmetic operators allowed: $operators
Math function allowed: $mathFunctions

Type "exit" or Ctrl-C to quit.
HEREDOC.PHP_EOL;
    }

    private function getOperators(): array
    {
        return $this->operators;
    }

    private function getMathFunctions(): array
    {
        return $this->mathFunctions;
    }

    /**
     * @throws InvalidInputException
     * @throws CalculatorException
     */
    public function validateOperation(string $line): void
    {
        $tokens = token_get_all('<?php '.$line);
        // Remove the opening PHP tag
        array_shift($tokens);

        if (empty($tokens)) {
            throw new InvalidInputException();
        }

        $allowedTokens = [
            T_LNUMBER,
            T_DNUMBER,
            T_POW,
            T_STRING, // for math functions
        ];

        foreach ($tokens as $token) {
            if (is_array($token)) {
                $constant = $token[0];
                $value = $token[1];

                if (!in_array($constant, $allowedTokens, true)) {
                    throw new InvalidInputException();
                }

                if ($constant === T_POW) {
                    if (!in_array($value, $this->getOperators(), true)) {
                        throw CalculatorException::invalidOperatorFunction();
                    }
                }

                if ($constant === T_STRING) {
                    if (!in_array($value, $this->getMathFunctions(), true)) {
                        throw CalculatorException::invalidMathFunction();
                    }
                }
            } else {
                if (!in_array($token, $this->getOperators(), true)) {
                    throw CalculatorException::invalidOperatorFunction();
                }
            }
        }
    }

    public function convertMathFunction(string $line, array $mathFunctions): string
    {
        $pattern = '/(\d+)('.implode('|', array_map('preg_quote', $mathFunctions)).')/';

        return preg_replace($pattern, '$2($1)', $line);
    }

    public function calculate(string $line): string
    {
        try {
            $result = eval('return '.$line.';');

            // https://floating-point-gui.de/languages/php
            if (is_float($result)) {
                $result = (float) number_format($result, 14, '.', '');
            }

            return (string) $result;
        } catch (Throwable $e) {
            return 'Error: '.$e->getMessage();
        }
    }
}