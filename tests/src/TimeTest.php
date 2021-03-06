<?php

namespace Robier\WorkingDay\Tests;

use DateTime;
use Generator;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Robier\WorkingDay\Time;

class TimeTest extends TestCase
{
    /**
     *
     */
    public function dataProviderFor24Hours(): Generator
    {
        for ($h = 0; $h < 24; ++$h) {
            for ($m = 0; $m < 60; ++$m) {
                yield [$h, $m];
            }
        }
    }

    /**
     * @return array
     */
    public function dataProviderForOverflowMinutes(): array
    {
        return
            [
                [14, 65, 15, 5],
                [2, 95, 3, 35],
                [5, 256, 9, 16],
            ];
    }

    /**
     * @return array
     */
    public function dataProviderForIntegerConversion(): array
    {
        return
            [
                [1440, 24, 0],
                [1, 0, 1],
                [55, 0, 55],
                [61, 1, 1],
            ];
    }

    public function dataProviderForTimeStringConversion(): array
    {
        return
            [
                ['18:00', 18, 0],
                ['9 AM', 9, 0],
                ['13:25:10', 13, 25],
                ['13:25:59', 13, 25],
                ['15:59', 15, 59],
            ];
    }

    public function dataProviderForStringConversion(): array
    {
        return
            [
                ['18:00', 18, 0],
                ['2:2', 2, 2],
                ['02:35', 2, 35],
                ['10:65', 11, 5],
                ['25:65', 26, 5],
            ];
    }

    public function dataProviderForStringConversionException(): array
    {
        return
            [
                ['a:90'],
                ['13:b'],
                ['aa:bb'],
                ['test'],
                [''],
            ];
    }

    /**
     * @dataProvider dataProviderFor24Hours
     *
     * @param $hours
     * @param $minutes
     */
    public function testAllPossibleTimesIn24Hours(int $hours, int $minutes): void
    {
        $time = new Time($hours, $minutes);

        $this->assertSame($hours, $time->hours());
        $this->assertSame($minutes, $time->minutes());
    }

    public function testNegativeHours(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new Time(-52, 10);
    }

    public function testNegativeMinutes(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new Time(52, -10);
    }

    public function testNegativeNumbersProvided(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new Time(-52, -10);
    }

    public function testPositiveNumbers(): void
    {
        $time = new Time(52, 10);

        $this->assertSame(52, $time->hours());
        $this->assertSame(10, $time->minutes());
    }

    /**
     * @dataProvider dataProviderForOverflowMinutes
     *
     * @param int $hours
     * @param int $minutes
     * @param int $correctHours
     * @param int $correctMinutes
     */
    public function testOverflowMinutes(int $hours, int $minutes, int $correctHours, int $correctMinutes): void
    {
        $time = new Time($hours, $minutes);

        $this->assertSame($correctHours, $time->hours());
        $this->assertSame($correctMinutes, $time->minutes());
    }

    public function testCurrentTime(): void
    {
        $time = Time::now();

        $currentTime = new DateTime();

        $this->assertSame((int) $currentTime->format('G'), $time->hours());
        $this->assertSame((int) $currentTime->format('i'), $time->minutes());
    }

    public function testDateProvided(): void
    {
        $date = new DateTime();
        $date->setTime(15, 35);

        $time = Time::dateTime($date);

        $this->assertSame((int) $date->format('G'), $time->hours());
        $this->assertSame((int) $date->format('i'), $time->minutes());
    }

    /**
     * @dataProvider dataProviderForIntegerConversion
     *
     * @param int $integer
     * @param int $hours
     * @param int $minutes
     */
    public function testConvertingTimeToInteger(int $integer, int $hours, int $minutes): void
    {
        $time = new Time($hours, $minutes);

        $this->assertSame($integer, $time->toInteger());
    }

    /**
     * @dataProvider dataProviderForIntegerConversion
     *
     * @param int $integer
     * @param int $hours
     * @param int $minutes
     */
    public function testConvertingIntegerToTime(int $integer, int $hours, int $minutes): void
    {
        $time = Time::integer($integer);

        $this->assertSame($hours, $time->hours());
        $this->assertSame($minutes, $time->minutes());
    }

    /**
     * @dataProvider dataProviderForTimeStringConversion
     *
     * @param string $time
     * @param int    $hours
     * @param int    $minutes
     */
    public function testConvertingTimeStringToTime(string $time, int $hours, int $minutes): void
    {
        $time = Time::time($time);

        $this->assertSame($hours, $time->hours());
        $this->assertSame($minutes, $time->minutes());
    }

    /**
     * @dataProvider dataProviderForStringConversion
     *
     * @param string $time
     * @param int    $hours
     * @param int    $minutes
     */
    public function testConvertingStringToTime(string $time, int $hours, int $minutes): void
    {
        $time = Time::string($time);

        $this->assertSame($hours, $time->hours());
        $this->assertSame($minutes, $time->minutes());
    }

    /**
     * @dataProvider dataProviderForStringConversion
     *
     * @param string $time
     */
    public function testConvertingTimeToString(string $time): void
    {
        $time = Time::string($time);

        $this->assertSame(sprintf('%02d:%02d', $time->hours(), $time->minutes()), (string) $time);
        $this->assertSame(sprintf('%02d:%02d', $time->hours(), $time->minutes()), $time->toString());
    }

    /**
     * @dataProvider dataProviderForStringConversionException
     *
     * @param string $time
     */
    public function testConvertingStringToTimeException(string $time): void
    {
        $this->expectException(InvalidArgumentException::class);

        Time::string($time);
    }
}
