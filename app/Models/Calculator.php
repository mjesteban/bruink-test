<?php

declare(strict_types=1);

namespace app\Models;

use Throwable;

class Calculator
{
    private array $operators = ['+', '-', '*', '/',];
    private array $mathFunctions = ['sqrt',];

    public function run(): void
    {
        $this->welcomeMessage();

        while (true) {
            $prompt = 'calc > ';
            $line = preg_replace('/\s/', '', (string) readline($prompt));

            if ($line === 'exit') {
                echo 'Bye!'.PHP_EOL;
                break;
            }

            if (!$this->isValidOperation($line)) {
                continue;
            }

            // Convert math functions e.g. "9sqrt" to sqrt(9);
            $line = $this->convertMathFunction($line, $this->getMathFunctions());

            try {
                $this->calculate($line);
                echo PHP_EOL;
            } catch (Throwable $e) {
                echo 'Error in operation: '.$e->getMessage().PHP_EOL;
            }
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

    private function isValidOperation(string $line): bool
    {
        $tokens = token_get_all('<?php '.$line);
        array_shift($tokens);

        $allowedTokens = [
            T_LNUMBER,
            T_DNUMBER,
            T_POW,
            T_STRING, // for math functions
        ];

        foreach ($tokens as $index => $token) {
            if (is_array($token)) {
                $constant = $token[0];
                $value = $token[1];

                if (!in_array($constant, $allowedTokens, true)) {
                    echo 'Invalid input.'.PHP_EOL;
                    return false;
                }

                if ($constant === T_POW) {
                    if (!in_array($value, $this->getOperators(), true)) {
                        echo 'Invalid operator.'.PHP_EOL;
                        return false;
                    }
                }

                if ($constant === T_STRING) {
                    if (!in_array($value, $this->getMathFunctions(), true)) {
                        echo 'Invalid math function.'.PHP_EOL;
                        return false;
                    }
                }
            } else {
                if (!in_array($token, $this->getOperators(), true)) {
                    echo 'Invalid operator.'.PHP_EOL;
                    return false;
                }
            }
        }

        return true;
    }

    private function convertMathFunction(string $line, array $mathFunctions): string
    {
        $pattern = '/(\d+)('.implode('|', array_map('preg_quote', $mathFunctions)).')/';

        return preg_replace($pattern, '$2($1)', $line);
    }

    private function calculate(string $line): void
    {
        $result = eval('return '.$line.';');

        // https://floating-point-gui.de/languages/php
        if (is_float($result)) {
            $result = (float) number_format($result, 10);
        }

        echo $result;
    }
}