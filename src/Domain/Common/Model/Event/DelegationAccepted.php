<?php

namespace Domain\Common\Model\Event;

class DelegationAccepted
{
    public $userId;
    public $businessId;

    public function __construct(string $userId, int $businessId)
    {
        $this->userId = $userId;
        $this->businessId = $businessId;
    }
}
