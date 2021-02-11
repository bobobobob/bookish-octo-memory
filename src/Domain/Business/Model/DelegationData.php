<?php

namespace Domain\Business\Model;

use App\EventAggregator;
use Domain\Common\Model\Event\DelegationAccepted;
use RuntimeException;
use Selective\ArrayReader\ArrayReader;

class DelegationData
{
    use EventAggregator;

    private $businessId;
    private $userId;
    private $accepted;

    public function isNew()
    {
        return $this->accepted === null;
    }

    public function businessId()
    {
        return $this->businessId;
    }

    public function userId()
    {
        return $this->userId;
    }

    public function accepted()
    {
        return $this->accepted;
    }

    public function getState()
    {
        return [
            'business' => $this->businessId,
            'user' => $this->userId,
            'accepted' => $this->accepted ?? 0,
        ];
    }

    public function accept()
    {
        if ($this->accepted !== 0) throw new RuntimeException('Delegation already accepted', 1);

        $this->accepted = 1;
        $this->recordThat(new DelegationAccepted($this->userId, $this->businessId));
    } 

    private function __construct()
    {
    }

    public static function create(int $businessId, string $userId)
    {
        $new = new self();
        $new->businessId = $businessId;
        $new->userId = $userId;
        return $new;
    }

    public static function fromState(array $state)
    {
        $reader = new ArrayReader($state);
        $new = new self();
        $new->businessId = $reader->getInt('business');
        $new->userId = $reader->getString('user');
        $new->accepted = $reader->findInt('accepted', 0);

        return $new;
    }
}
