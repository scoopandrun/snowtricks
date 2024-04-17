<?php

namespace App\Tests\Utils;

use App\Utils\QuantityFormatter;
use PHPUnit\Framework\TestCase;

class QuantityFormatterTest extends TestCase
{
    public function testFormatQuantityNoUnit()
    {
        $this->assertEquals('100', QuantityFormatter::formatQuantity(100));
        $this->assertEquals('1024', QuantityFormatter::formatQuantity(1024));
        $this->assertEquals('1048576', QuantityFormatter::formatQuantity(1048576));
    }

    public function testFormatQuantityWithUnit()
    {
        $this->assertEquals('100B', QuantityFormatter::formatQuantity(100, 'B'));
        $this->assertEquals('1KB', QuantityFormatter::formatQuantity(1024, 'KB'));
        $this->assertEquals('1MB', QuantityFormatter::formatQuantity(1048576, 'MB'));
        $this->assertEquals('1GB', QuantityFormatter::formatQuantity(1073741824, 'GB'));
        $this->assertEquals('1TB', QuantityFormatter::formatQuantity(1099511627776, 'TB'));
    }

    public function testFormatQuantityAutoUnit()
    {
        $this->assertEquals('100B', QuantityFormatter::formatQuantity(100, 'auto'));
        $this->assertEquals('1KB', QuantityFormatter::formatQuantity(1024, 'auto'));
        $this->assertEquals('1MB', QuantityFormatter::formatQuantity(1048576, 'auto'));
        $this->assertEquals('1GB', QuantityFormatter::formatQuantity(1073741824, 'auto'));
        $this->assertEquals('1TB', QuantityFormatter::formatQuantity(1099511627776, 'auto'));
    }

    public function testFormatQuantityDisplayUnitFalse()
    {
        $this->assertEquals('100', QuantityFormatter::formatQuantity(100, 'B', false));
        $this->assertEquals('1', QuantityFormatter::formatQuantity(1024, 'KB', false));
        $this->assertEquals('1', QuantityFormatter::formatQuantity(1048576, 'MB', false));
        $this->assertEquals('1', QuantityFormatter::formatQuantity(1073741824, 'GB', false));
        $this->assertEquals('1', QuantityFormatter::formatQuantity(1099511627776, 'TB', false));
    }

    public function testFormatQuantityInvalidUnit()
    {
        // Invalid unit should default to bytes
        $this->assertEquals('100B', QuantityFormatter::formatQuantity(100, 'Z'));
    }

    public function testFormatQuantityStringInput()
    {
        // Test string input
        $this->assertEquals('100B', QuantityFormatter::formatQuantity('100B'));
        $this->assertEquals('1KB', QuantityFormatter::formatQuantity('1KB'));
        $this->assertEquals('1MB', QuantityFormatter::formatQuantity('1MB'));
        $this->assertEquals('1GB', QuantityFormatter::formatQuantity('1GB'));
        $this->assertEquals('1TB', QuantityFormatter::formatQuantity('1TB'));
    }
}
