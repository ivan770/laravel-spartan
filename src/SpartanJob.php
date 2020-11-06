<?php

namespace Ivan770\Spartan;

use GuzzleHttp\ClientInterface;
use Illuminate\Container\Container;
use Illuminate\Contracts\Queue\Job as JobContract;
use Illuminate\Queue\Jobs\Job;

class SpartanJob extends Job implements JobContract
{
    use InteractsWithQueue;

    /**
     * HTTP client instance.
     *
     * @var ClientInterface
     */
    protected $client;

    /**
     * Spartan MQ response contents
     *
     * @var array
     */
    protected $raw;

    public function __construct(Container $container, ClientInterface $client, array $raw, $connectionName, $queue)
    {
        $this->container = $container;
        $this->client = $client;
        $this->raw = $raw;
        $this->connectionName = $connectionName;
        $this->queue = $queue;
    }

    public function release($delay = 0)
    {
        parent::release($delay);

        $this->client->request('POST', $this->getQueueUri($this->queue, 'requeue'), [
            'json' => [
                // Worth noticing that Spartan doesn't support refreshing delay after requeue (yet)
                'id' => $this->getJobId()
            ]
        ]);
    }

    public function delete()
    {
        parent::delete();

        $this->client->request('DELETE', $this->getQueueUri($this->queue), [
            'json' => [
                'id' => $this->getJobId()
            ]
        ]);
    }

    public function attempts()
    {
        return $this->raw['state']['tries'];
    }

    public function getJobId()
    {
        return $this->raw['id'];
    }

    public function getRawBody()
    {
        return $this->raw['body'];
    }

    public function getRaw()
    {
        return $this->raw;
    }
}