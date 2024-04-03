<?php

use App\Utils\QuantityFormatter;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class QuantityFormatterTest extends TestCase
{
    /**
     * @dataProvider valueProvider
     */
    public function testQuantitiesAreProperlyFormatted(
        string|int $value,
        ?string $unit,
        bool $displayUnit,
        string $expected
    ): void {
        $this->assertSame($expected, QuantityFormatter::formatQuantity($value, $unit, $displayUnit));
    }

    public static function valueProvider(): \Generator
    {
        yield [0, null, true, '0B'];
        yield [0, 'B', true, '0B'];
        yield [0, 'K', true, '0KB'];
        yield [0, 'M', true, '0MB'];
        yield [0, 'G', true, '0GB'];
        yield [0, 'auto', true, '0B'];
        yield [0, null, false, '0'];
        yield [0, 'B', false, '0'];
        yield [0, 'K', false, '0'];
        yield [0, 'M', false, '0'];
        yield [0, 'G', false, '0'];
        yield [0, 'auto', false, '0'];

        yield [1, null, true, '1B'];
        yield [1, 'B', true, '1B'];
        yield [1, 'K', true, '0KB'];
        yield [1, 'M', true, '0MB'];
        yield [1, 'G', true, '0GB'];
        yield [1, 'auto', true, '1B'];
        yield [1, null, false, '1'];
        yield [1, 'B', false, '1'];
        yield [1, 'K', false, '0'];
        yield [1, 'M', false, '0'];
        yield [1, 'G', false, '0'];
        yield [1, 'auto', false, '1'];

        yield [1023, null, true, '1023B'];
        yield [1023, 'B', true, '1023B'];
        yield [1023, 'K', true, '0KB'];
        yield [1023, 'M', true, '0MB'];
        yield [1023, 'G', true, '0GB'];
        yield [1023, 'auto', true, '1023B'];
        yield [1023, null, false, '1023'];
        yield [1023, 'B', false, '1023'];
        yield [1023, 'K', false, '0'];
        yield [1023, 'M', false, '0'];
        yield [1023, 'G', false, '0'];
        yield [1023, 'auto', false, '1023'];

        yield [1024, null, true, '1024B'];
        yield [1024, 'B', true, '1024B'];
        yield [1024, 'K', true, '1KB'];
        yield [1024, 'M', true, '0MB'];
        yield [1024, 'G', true, '0GB'];
        yield [1024, 'auto', true, '1KB'];
        yield [1024, null, false, '1024'];
        yield [1024, 'B', false, '1024'];
        yield [1024, 'K', false, '1'];
        yield [1024, 'M', false, '0'];
        yield [1024, 'G', false, '0'];
        yield [1024, 'auto', false, '1'];

        yield [4096, null, true,  '4096B'];
        yield [4096, 'B', true,  '4096B'];
        yield [4096, 'K', true, '4KB'];
        yield [4096, 'M', true, '0MB'];
        yield [4096, 'G', true, '0GB'];
        yield [4096, 'auto', true, '4KB'];
        yield [4096, null, false,  '4096'];
        yield [4096, 'B', false,  '4096'];
        yield [4096, 'K', false, '4'];
        yield [4096, 'M', false, '0'];
        yield [4096, 'G', false, '0'];
        yield [4096, 'auto', false, '4'];

        yield [2147483648, null, true, '2147483648B'];
        yield [2147483648, 'B', true, '2147483648B'];
        yield [2147483648, 'K', true, '2097152KB'];
        yield [2147483648, 'M', true, '2048MB'];
        yield [2147483648, 'G', true, '2GB'];
        yield [2147483648, 'auto', true, '2GB'];
        yield [2147483648, null, false, '2147483648'];
        yield [2147483648, 'B', false, '2147483648'];
        yield [2147483648, 'K', false, '2097152'];
        yield [2147483648, 'M', false, '2048'];
        yield [2147483648, 'G', false, '2'];
        yield [2147483648, 'auto', false, '2'];

        yield ['3K', null, true,  '3KB'];
        yield ['3K', 'B', true,  '3072B',];
        yield ['3K', 'K', true, '3KB'];
        yield ['3K', 'M', true, '0MB'];
        yield ['3K', 'G', true, '0GB'];
        yield ['3K', 'auto', true, '3KB'];
        yield ['3K', null, false,  '3K'];
        yield ['3K', 'B', false,  '3072',];
        yield ['3K', 'K', false, '3'];
        yield ['3K', 'M', false, '0'];
        yield ['3K', 'G', false, '0'];
        yield ['3K', 'auto', false, '3'];

        yield ['1024K', null, true, '1024KB'];
        yield ['1024K', 'B', true, '1048576B'];
        yield ['1024K', 'K', true, '1024KB'];
        yield ['1024K', 'M', true, '1MB'];
        yield ['1024K', 'G', true, '0GB'];
        yield ['1024K', 'auto', true, '1MB'];
        yield ['1024K', null, false, '1024K'];
        yield ['1024K', 'B', false, '1048576'];
        yield ['1024K', 'K', false, '1024'];
        yield ['1024K', 'M', false, '1'];
        yield ['1024K', 'G', false, '0'];
        yield ['1024K', 'auto', false, '1'];

        yield ['2M', null, true, '2MB'];
        yield ['2M', 'B', true, '2097152B'];
        yield ['2M', 'K', true, '2048KB'];
        yield ['2M', 'M', true, '2MB'];
        yield ['2M', 'G', true, '0GB'];
        yield ['2M', 'auto', true, '2MB'];
        yield ['2M', null, false, '2M'];
        yield ['2M', 'B', false, '2097152'];
        yield ['2M', 'K', false, '2048'];
        yield ['2M', 'M', false, '2'];
        yield ['2M', 'G', false, '0'];
        yield ['2M', 'auto', false, '2'];

        yield ['2G', null, true,  '2GB'];
        yield ['2G', 'B', true, '2147483648B'];
        yield ['2G', 'K', true, '2097152KB'];
        yield ['2G', 'M', true, '2048MB'];
        yield ['2G', 'G', true, '2GB'];
        yield ['2G', 'auto', true, '2GB'];
        yield ['2G', null, false,  '2G'];
        yield ['2G', 'B', false, '2147483648'];
        yield ['2G', 'K', false, '2097152'];
        yield ['2G', 'M', false, '2048'];
        yield ['2G', 'G', false, '2'];
        yield ['2G', 'auto', false, '2'];
    }
}
