#!/usr/bin/env php
<?php

require __DIR__.'/vendor/autoload.php';

use Adombrovsky\ImageProcessorBot\Commands\SchedulerCommand;
use Adombrovsky\ImageProcessorBot\Commands\DownloaderCommand;
use Symfony\Component\Console\Application;


$settings = require __DIR__.'/config.php';


foreach ($settings['redis'] as $key => $value) {
    putenv(sprintf('%s=%s', $key, $value));
}


$application = new Application();
$application->add(new SchedulerCommand());
$application->add(new DownloaderCommand());
$application->run();