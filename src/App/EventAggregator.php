<?php

namespace App;

trait EventAggregator
{
    private $events = [];

    public function releaseEvents()
    {
        $events = $this->events;
        $this->events = [];

        return $events;
    }

    protected function recordThat(object $event)
    {
        $this->events[] = $event;
    }
}
