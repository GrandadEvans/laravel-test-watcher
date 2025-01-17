<?php

namespace Grandadevans\LaravelTestWatcher\Tests;

use Orchestra\Testbench\TestCase;
use Illuminate\Support\Facades\Artisan;
use Grandadevans\LaravelTestWatcher\Contracts\PHPUnitRunnerContract;
use Grandadevans\LaravelTestWatcher\TestFiles\FilesToTestRepository;
use Grandadevans\LaravelTestWatcher\LaravelTestWatcherServiceProvider;
use Grandadevans\LaravelTestWatcher\Factories\LaravelTestWatcherFactory;
use Grandadevans\LaravelTestWatcher\Contracts\CommandLineInterfaceContract;
use Grandadevans\LaravelTestWatcher\Facades\LaravelTestWatcher as LaravelTestWatcherFacade;

class LaravelTestWatcherTest extends TestCase
{
    protected function getPackageProviders($app)
    {
        return [LaravelTestWatcherServiceProvider::class];
    }

    protected function getPackageAliases($app)
    {
        return [
            'LaravelTestWatcher' => LaravelTestWatcherFacade::class,
        ];
    }

    protected function getBasePath()
    {
        return __DIR__.'/helpers';
    }

    /**
     * @test
     */
    public function it_can_be_started_through_an_artisan_command()
    {
        $this->withoutMockingConsoleOutput();
        LaravelTestWatcherFacade::shouldReceive('watch')->once()->andReturnNull();

        $this->artisan('tests:watch');
        $output = Artisan::output();

        $this->assertEquals('Starting test watcher...'.PHP_EOL, $output);
    }

    /** @test */
    public function it_prepares_by_running_through_filesystem_and_update_files_to_test_repository()
    {
        $testFileRepoMock = \Mockery::mock(FilesToTestRepository::class);
        $testFileRepoMock->shouldReceive('update')->andReturnNull();
        app()->instance(FilesToTestRepository::class, $testFileRepoMock);
        $cliMock = \Mockery::mock(CommandLineInterfaceContract::class);
        $cliMock->shouldReceive('render')->andReturnNull();
        app()->instance(CommandLineInterfaceContract::class, $cliMock);
        $phpunitMock = \Mockery::mock(PHPUnitRunnerContract::class);
        $phpunitMock->shouldReceive('run')->andReturnNull();
        app()->instance(PHPUnitRunnerContract::class, $phpunitMock);
        $watcher = LaravelTestWatcherFactory::create();

        $watcher->prepare();
    }
}
