<?php

namespace Grandadevans\LaravelTestWatcher\Tests\helpers\tests;

use PHPUnit\Framework\TestCase;

class TestOne extends TestCase
{
    /**
     * @test
     * @watch
     */
    public function it_serves_as_a_fake_test_for_a_real_test()
    {
        $this->assertTrue(true);
    }
}
