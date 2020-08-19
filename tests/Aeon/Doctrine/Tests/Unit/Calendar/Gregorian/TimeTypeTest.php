<?php

declare(strict_types=1);

namespace Aeon\Doctrine\Tests\Unit\Calendar\Gregorian;

use Aeon\Calendar\Gregorian\Time;
use Aeon\Doctrine\Calendar\Gregorian\TimeType;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\ConversionException;
use Doctrine\DBAL\Types\Type;
use PHPUnit\Framework\TestCase;

final class TimeTypeTest extends TestCase
{
    protected function setUp() : void
    {
        if (!Type::hasType(TimeType::NAME)) {
            Type::addType(TimeType::NAME, TimeType::class);
        }
    }

    public function test_converting_valid_values() : void
    {
        $type = Type::getType(TimeType::NAME);

        $stringTime = $type->convertToDatabaseValue($time = new Time(15, 10, 5, 1), $this->createPlatformMock());
        $timeConverted = $type->convertToPHPValue($stringTime, $this->createPlatformMock());

        $this->assertSame('15:10:05.000001', $stringTime);
        $this->assertEquals($time, $timeConverted);
    }

    public function test_converting_null() : void
    {
        $type = Type::getType(TimeType::NAME);

        $this->assertNull($type->convertToDatabaseValue(null, $this->createPlatformMock()));
        $this->assertNull($type->convertToPHPValue(null, $this->createPlatformMock()));
    }

    public function test_converting_invalid_value_to_database_value() : void
    {
        $type = Type::getType(TimeType::NAME);

        $this->expectException(ConversionException::class);
        $this->expectExceptionMessage('Could not convert PHP value \'invalid time\' of type \'string\' to type \'aeon_time\'. Expected one of the following types: null, Time');
        $type->convertToDatabaseValue('invalid time', $this->createPlatformMock());
    }

    public function test_converting_invalid_value_to_php_value() : void
    {
        $type = Type::getType(TimeType::NAME);

        $this->expectException(ConversionException::class);
        $this->expectExceptionMessage('Could not convert database value "invalid time" to Doctrine Type aeon_time. Expected format: H:i:s');
        $type->convertToPHPValue('invalid time', $this->createPlatformMock());
    }

    /**
     * @return AbstractPlatform
     */
    private function createPlatformMock() : object
    {
        $mock = $this->createMock(AbstractPlatform::class);
        $mock->method('getTimeFormatString')
            ->willReturn('H:i:s');

        return $mock;
    }
}
