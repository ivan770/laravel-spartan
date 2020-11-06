<?php

namespace Ivan770\Spartan;

trait InteractsWithQueue
{
    /**
     * Get prepared URI suffix for queue
     *
     * @param  null  $queue
     * @param  string|null  $suffix
     */
    protected function getQueueUri($queue = null, $suffix = null)
    {
        $queue = $queue ?? $this->default;

        if($suffix) {
            $queue .= "/{$suffix}";
        }

        return $queue;
    }
}