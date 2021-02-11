<?php

namespace Domain\Business\Service;

use App\EventDispatcher;
use Domain\Business\Model\DelegationData;
use Domain\Common\Model\MobilePhoneLogin;
use Domain\Common\Model\Event\DelegationDeclined;
use Domain\Common\Model\Event\Envelope;
use RuntimeException;

class Delegator
{
    private $delegationRepository;
    private $eventDispatcher;

    public function __construct(DelegateRepository $delegationRepository, EventDispatcher $eventDispatcher)
    {
        $this->delegationRepository = $delegationRepository;
        $this->eventDispatcher = $eventDispatcher;
    }

    public function delegate(string $ownerId, int $businessId, string $login)
    {
        $login = MobilePhoneLogin::fromString($login);

        $userId = $this->delegationRepository->findDelegate($login);

        if ($userId === $ownerId) throw new RuntimeException('Couldn`t delegate to self', 3);

        if (!$userId) {
            //throw new RuntimeException(sprintf('Login not found %s', $login), 4);
            $this->delegationRepository->saveInvitation($businessId, (string) $login);
            return;
        }

        $delegationData = $this->delegationRepository->findOne($businessId, $userId);

        if ($delegationData) {
            throw new RuntimeException('Already delegated to this user', 3);
        }

        $this->delegationRepository->save(DelegationData::create($businessId, $userId));
    }

    public function undelegate(int $businessId, string $login)
    {
        if ($data = $this->delegationRepository->findOneByLogin($businessId, MobilePhoneLogin::fromString($login))) {
            $this->delegationRepository->delete($data);
            $this->eventDispatcher->dispatch(
                Envelope::enclose(new DelegationDeclined($data->userId(), $businessId))
            );
        } else {
            $this->delegationRepository->deleteInvitation($businessId, $login);
        }
    }

    public function accept(string $userId, int $businessId)
    {
        $data = $this->delegationRepository->getOne($businessId, $userId);

        $data->accept();

        $this->delegationRepository->save($data);

        $this->eventDispatcher->dispatchAll(
            \array_map(
                function ($event) {
                    return Envelope::enclose($event);
                },
                $data->releaseEvents()
            )
        );
    }

    public function decline(string $userId, int $businessId)
    {
        $data = $this->delegationRepository->getOne($businessId, $userId);

        $this->delegationRepository->delete($data);

        $this->eventDispatcher->dispatch(
            Envelope::enclose(new DelegationDeclined($userId, $businessId))
        );
    }

    public function list(int $businessId, array $input)
    {
        return $this->delegationRepository->listbyBusiness($businessId, $input);
    }
}
