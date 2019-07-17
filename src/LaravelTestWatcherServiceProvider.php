<?php

namespace Grandadevans\LaravelTestWatcher;

use League\CLImate\CLImate;
use React\EventLoop\Factory;
use React\EventLoop\LoopInterface;
use Illuminate\Support\ServiceProvider;
use Grandadevans\LaravelTestWatcher\Console\TestWatcherCommand;
use Grandadevans\LaravelTestWatcher\Contracts\PHPUnitRunnerContract;
use Grandadevans\LaravelTestWatcher\TestFiles\FilesToTestRepository;
use Grandadevans\LaravelTestWatcher\Factories\LaravelTestWatcherFactory;
use Grandadevans\LaravelTestWatcher\Finders\TestsAnnotatedWithWatchFinder;
use Grandadevans\LaravelTestWatcher\Contracts\AnnotatedTestsFinderContract;
use Grandadevans\LaravelTestWatcher\Contracts\CommandLineInterfaceContract;
use Grandadevans\LaravelTestWatcher\CommandLineInterface\CommandLineInterface;

class LaravelTestWatcherServiceProvider extends ServiceProvider
{
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/config.php' => config_path('laravel-test-watcher.php'),
            ], 'config');
            $this->commands([
                TestWatcherCommand::class,
            ]);
        }
    }

    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/config.php', 'laravel-test-watcher');

        $this->app->singleton('laravel-test-watcher', function () {
            return ( new LaravelTestWatcherFactory )->make();
        });

        $this->app->singleton(FilesToTestRepository::class, function () {
            return  new FilesToTestRepository(app(AnnotatedTestsFinderContract::class));
        });

        $this->app->singleton(CommandLineInterfaceContract::class, function () {
            return new CommandLineInterface(app(FilesToTestRepository::class), new CLImate);
        });

        $this->app->singleton(PHPUnitRunnerContract::class, function () {
            return new PHPUnitRunner(app(FilesToTestRepository::class), app(CommandLineInterfaceContract::class));
        });

        $this->app->bind(AnnotatedTestsFinderContract::class, function () {
            return new TestsAnnotatedWithWatchFinder;
        });

        $this->app->bind(LoopInterface::class, function () {
            return Factory::create();
        });
    }
}
