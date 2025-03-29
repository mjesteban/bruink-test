<?php

declare(strict_types=1);

namespace App\Models;

use App\Exceptions\CalculatorException;
use App\Exceptions\InvalidInputException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class CalculatorTest extends TestCase
{
    private Calculator $calculator;

    public static function provideValidOperations(): array
    {
        return [
            ['2+2', '4'],
            ['5-3', '2'],
            ['4*2', '8'],
            ['8/2', '4'],
            ['9 sqrt', '3'],
            ['9sqrt', '3'],
        ];
    }

    public static function provideInvalidOperations(): array
    {
        return [
            ['2++2'],
            ['5--3'],
            ['4**2'],
            ['8//2'],
            ['9unknown'],
        ];
    }

    /**
     * @throws InvalidInputException
     * @throws CalculatorException
     */
    #[DataProvider('provideValidOperations')]
    public function testValidOperations(string $operation, string $expected): void
    {
        $this->calculator->validateOperation($operation);
        $result = $this->calculator->calculate($operation);
        $this->assertSame($expected, $result);
    }

    /**
     * @throws CalculatorException
     */
    #[DataProvider('provideInvalidOperations')]
    public function testInvalidOperations(string $operation): void
    {
        $this->expectException(InvalidInputException::class);
        $this->calculator->validateOperation($operation);
    }

    protected function setUp(): void
    {
        $this->calculator = new Calculator(
            operators: ['+', '-', '*', '/'],
            mathFunctions: ['sqrt']
        );
    }
}