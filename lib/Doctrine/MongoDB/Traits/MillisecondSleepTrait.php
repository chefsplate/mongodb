<?php namespace Doctrine\MongoDB\Traits;

trait MillisecondSleepTrait
{
    /**
     * @param int $ms
     * @return void
     */
    protected function sleepForMs($ms)
    {
        usleep($ms * 1000);
    }
}
