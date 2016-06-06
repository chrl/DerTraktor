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
class DownloaderCommand extends Command
{
    protected function configure()
    {
        $this->setName('ipb:downloader')
            ->setAliases(['downloader'])
            ->addArgument('path', InputArgument::OPTIONAL, '', __DIR__.'/../../../../data/output')
            ->setDescription('Downloads images to the local temporary folder');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $path = $input->getArgument('path');

        $jobs = \Resque::size(DownloadJob::READY_QUEUE);
        while ($jobs > 0) {
            $jobInfo = \Resque::pop(DownloadJob::READY_QUEUE);

            if (isset($jobInfo['class']) && isset($jobInfo['args'])) {

                /** @var DownloadJob $job */
                $job = new $jobInfo['class']($jobInfo['args'][0]);

                try {
                    $job->perform($path);
                    Resque::enqueue(DownloadJob::DONE_QUEUE, DownloadJob::class, $jobInfo['args'][0], true);
                } catch (\Exception $e) {
                    $output->writeln($e->getMessage());
                    $id = Resque::enqueue(DownloadJob::FAILED_QUEUE, DownloadJob::class, $jobInfo['args'][0], true);

                    $output->writeln('Job with ID: '. $jobInfo['id']. 'added to FAILED queue. New ID:'.$id);
                }
            }

            $jobs--;
        }
    }
}
