<?php

namespace Grandadevans\LaravelTestWatcher\Contracts;

interface PHPUnitRunnerContract
{
    /**
     * @return void
     */
    public function run();

    /**
     * @return bool
     */
    public function isRunning();
}
