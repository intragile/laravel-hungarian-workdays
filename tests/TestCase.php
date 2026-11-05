<?php

declare(strict_types=1);

namespace IntrAgile\HungarianWorkdays\Tests;

use Illuminate\Testing\PendingCommand;
use IntrAgile\HungarianWorkdays\Laravel\HungarianWorkdaysServiceProvider;
use LogicException;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [HungarianWorkdaysServiceProvider::class];
    }

    /**
     * @param  array<string, mixed>  $parameters
     */
    protected function command(string $name, array $parameters = []): PendingCommand
    {
        $command = $this->artisan($name, $parameters);

        if (! $command instanceof PendingCommand) {
            throw new LogicException('artisan() did not return a pending command.');
        }

        return $command;
    }
}
