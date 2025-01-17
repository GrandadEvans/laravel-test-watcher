<?php

namespace Grandadevans\LaravelTestWatcher;

use Symfony\Component\Process\Process;
use Grandadevans\LaravelTestWatcher\TestFiles\TestFile;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Grandadevans\LaravelTestWatcher\Contracts\PHPUnitRunnerContract;
use Grandadevans\LaravelTestWatcher\TestFiles\FilesToTestRepository;
use Grandadevans\LaravelTestWatcher\Contracts\CommandLineInterfaceContract;
use Grandadevans\LaravelTestWatcher\CommandLineInterface\CommandLineInterface;

class PHPUnitRunner implements PHPUnitRunnerContract
{
    private $isRunningTests = false;

    /**
     * @var FilesToTestRepository
     */
    private $filesToTestRepository;

    /**
     * @var CommandLineInterface
     */
    private $cli;

    /**
     * PHPUnitRunner constructor.
     *
     * @param FilesToTestRepository $filesToTestRepository
     * @param CommandLineInterfaceContract $cli
     */
    public function __construct(FilesToTestRepository $filesToTestRepository, CommandLineInterfaceContract $cli)
    {
        $this->filesToTestRepository = $filesToTestRepository;
        $this->cli = $cli;
    }

    public function run()
    {
        if ($this->filesToTestRepository->getFilesToTest()->count() === 0) {
            return;
        }

        $this->isRunningTests = true;

        $this->filesToTestRepository->getFilesToTest()->each(function (TestFile $test) {
            $test->resetStatuses();

            foreach ($test->getMethodsToWatch() as $key=>$method) {
                // Make a quick and dirty change that will hopefully fire codeception instead of phpunit
                // @todo: I will try and get it done properly in the coming week or so
                $process = new Process([base_path().'/vendor/bin/codecept', '', $method, $test->getFilePath()], base_path());

                try {
                    $process->mustRun();
                    $test->addPassedTest($method);
                } catch (ProcessFailedException $exception) {
                    $test->addFailedTest($method, $exception->getProcess()->getOutput());
                }

                $this->cli->render();

                if ($key == array_keys($test->getMethodsToWatch())[count($test->getMethodsToWatch()) - 1]) {
                    $this->isRunningTests = false;
                }
            }
        });
    }

    public function isRunning()
    {
        return $this->isRunningTests;
    }
}
