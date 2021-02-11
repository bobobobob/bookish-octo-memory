<?php

namespace Domain\Common\Model\Event;

use Exception;

class Envelope
{
    private $event;

    private function __constructor() {}

    public static function enclose(object $event) {
        $new = new self();
        $new->event = $event;
        return $new;
    }

    public static function unserialize(string $serialized)
    {
        $new = \unserialize($serialized);

        if (\is_object($new) && $new instanceof Envelope) return $new;

        throw new Exception("Bad envelope found", 1);
    }

    public function unwrap()
    {
        return $this->event;
    }

    public function serialize()
    {
        return serialize($this);
    }
}
