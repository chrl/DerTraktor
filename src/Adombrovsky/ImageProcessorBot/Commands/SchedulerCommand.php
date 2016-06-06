<?php

namespace Adombrovsky\ImageProcessorBot\Commands;

use Adombrovsky\ImageProcessorBot\Jobs\DownloadJob;
use Resque;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Created by PhpStorm.
 * User: adombrovsky
 * Date: 6/5/16
 * Time: 7:33 PM
 */
class SchedulerCommand extends Command
{

    protected function configure()
    {
        $this->setName('ipb:scheduler')
            ->setAliases(['scheduler'])
            ->addArgument('file', InputArgument::REQUIRED)
            ->setDescription('Accepts a file with list of URLs to download and schedule them for download');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $filePath = $input->getArgument('file');

        if (!file_exists($filePath)) {
            throw new \RuntimeException(sprintf('File not found: %s', $filePath));
        }

        $images = file($input->getArgument('file'), FILE_IGNORE_NEW_LINES);

        if (!is_array($images)) {
            throw new \RuntimeException('Incorrect file structure.');
        }

        foreach ($images as $image) {
            if (filter_var($image, FILTER_VALIDATE_URL) === false) {
                $id = Resque::enqueue(DownloadJob::FAILED_QUEUE, DownloadJob::class, ['url'=>$image], true);
            } else {
                $id = Resque::enqueue(DownloadJob::READY_QUEUE, DownloadJob::class, ['url'=>$image], true);
            }
            $output->writeln('Added new url with id: '. $id);
        }
    }
}
