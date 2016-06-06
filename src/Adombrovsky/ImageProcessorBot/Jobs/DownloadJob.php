<?php
/**
 * Created by PhpStorm.
 * User: adombrovsky
 * Date: 6/6/16
 * Time: 12:08 AM
 */

namespace Adombrovsky\ImageProcessorBot\Jobs;

use Guzzle\Http\Client;

class DownloadJob
{
    const READY_QUEUE = 'download';
    const DONE_QUEUE = 'done';
    const FAILED_QUEUE = 'failed';

    private $args;

    public function __construct($args)
    {

        $this->args = $args;
    }

    /**
     * @param $path
     * @throws \Exception
     */
    public function perform($path)
    {
        $url = $this->args['url'];

        $client = new Client();
        $request = $client->get($url);
        $response = $request->send();

        if (!is_dir($path) || !is_writable($path)) {
            throw  new \Exception('Unable to write file to dir: '.$path);
        }

        file_put_contents($path.'/'.uniqid(), $response->getBody());

    }
}
