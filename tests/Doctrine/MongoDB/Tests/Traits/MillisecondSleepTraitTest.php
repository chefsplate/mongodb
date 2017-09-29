<?php namespace Doctrine\MongoDB\Tests\Traits;

use Doctrine\MongoDB\Tests\TestCase;
use Doctrine\MongoDB\Traits\MillisecondSleepTrait;

class MillisecondSleepTraitTest extends TestCase
{
    use MillisecondSleepTrait;

    public function testSleepForMilliseconds()
    {
        $sleep_time_ms = 100;

        $start_time_ms = round(microtime(true) * 1000);
        $this->sleepForMs($sleep_time_ms);
        $end_time_ms = round(microtime(true) * 1000);

        $execution_time_ms = round($end_time_ms - $start_time_ms);
        $this->assertGreaterThanOrEqual($sleep_time_ms, $execution_time_ms);
    }
}
