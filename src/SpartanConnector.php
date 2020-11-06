<?php

namespace Ivan770\Spartan;

use Exception;
use GuzzleHttp\ClientInterface;
use Illuminate\Queue\Connectors\ConnectorInterface;

class SpartanConnector implements ConnectorInterface
{
    /**
     * HTTP client instance.
     *
     * @var ClientInterface
     */
    protected $client;

    public function __construct(ClientInterface $client)
    {
        $this->client = $client;
    }

    public function connect(array $config)
    {
        if(empty($config['queue'])) {
            throw new Exception("Connecting to Spartan without queue name is not allowed");
        }

        return new SpartanQueue($this->client, $config['queue']);
    }
}