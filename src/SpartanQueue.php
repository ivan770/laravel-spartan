<?php

namespace Ivan770\Spartan;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Contracts\Queue\ClearableQueue;
use Illuminate\Contracts\Queue\Queue as QueueContract;
use Illuminate\Queue\Queue;

class SpartanQueue extends Queue implements QueueContract, ClearableQueue
{
    use InteractsWithQueue;

    /**
     * HTTP client instance.
     *
     * @var ClientInterface
     */
    protected $client;

    /**
     * Name of default queue.
     *
     * @var string
     */
    protected $default;

    public function __construct(ClientInterface $client, $default = '')
    {
        $this->client = $client;
        $this->default = $default;
    }

    /**
     * Get the size of queue.
     *
     * @param  string|null  $queue
     * @return int
     */
    public function size($queue = null)
    {
        $response = $this->client
            ->request('GET', $this->getQueueUri($queue, 'size'))
            ->getBody();

        return (int) json_decode($response)->size;
    }

    /**
     * Push a new job onto the queue.
     *
     * @param  string  $job
     * @param  mixed $data
     * @param  string|null  $queue
     * @return mixed
     */
    public function push($job, $data = '', $queue = null)
    {
        return $this->pushRaw($this->createPayload($job, $this->getQueueUri($queue), $data), $queue);
    }

    /**
     * Push a raw payload onto the queue.
     *
     * @param  string  $payload
     * @param  string|null  $queue
     * @param  array  $options
     * @return mixed
     */
    public function pushRaw($payload, $queue = null, array $options = [])
    {
        return json_decode($this->client
            ->request('POST', $this->getQueueUri($queue), [
                'json' => [
                    'body' => $payload,
                ]
            ])
            ->getBody(), true);
    }

    /**
     * Push a new job onto the queue after a delay.
     *
     * @param  \DateTimeInterface|\DateInterval|int  $delay
     * @param  string  $job
     * @param  mixed  $data
     * @param  string|null  $queue
     * @return mixed
     */
    public function later($delay, $job, $data = '', $queue = null)
    {
        return json_decode($this->client
            ->request('POST', $this->getQueueUri($queue), [
                'json' => [
                    'body' => $this->createPayload($job, $this->getQueueUri($queue), $data),
                    'delay' => $this->secondsUntil($delay)
                ]
            ])
            ->getBody(), true);
    }

    /**
     * Pop the next job off of the queue.
     *
     * @param  string|null  $queue
     * @return \Illuminate\Contracts\Queue\Job|null
     */
    public function pop($queue = null)
    {
        try {
            $response = $this->client
                ->request('GET', $this->getQueueUri($queue))
                ->getBody();
        } catch (ClientException $clientException) {
            $message = $clientException->getResponse()
                ->getBody()
                ->getContents();

            if($message == 'No message available') {
                return null;
            }

            throw $clientException;
        }

        return new SpartanJob(
            $this->container,
            $this->client,
            json_decode($response, true)['message'],
            $this->connectionName,
            $queue
        );
    }

    /**
     * Delete all of the jobs from the queue.
     *
     * @param  string  $queue
     * @return int
     */
    public function clear($queue)
    {
        return tap($this->size($queue), function () use ($queue) {
            $this->client->request('POST', $this->getQueueUri($queue, 'clear'));
        });
    }
}