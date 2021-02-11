<?php

namespace Domain\Common\Model\Event;

class DelegationDeclined
{
    public $userId;
    public $businessId;

    public function __construct(string $userId, int $businessId)
    {
        $this->userId = $userId;
        $this->businessId = $businessId;
    }
}
