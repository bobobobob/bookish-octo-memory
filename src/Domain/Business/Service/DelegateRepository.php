<?php

namespace Domain\Business\Service;

use App\Factory\QueryFactory;
use Domain\Business\Model\DelegationData;
use Domain\Common\Model\Login;
use Domain\Common\Model\RepositoryTransaction;
use InvalidArgumentException;

class DelegateRepository
{
    use RepositoryTransaction;
    /**
     * @var QueryFactory
     */
    private $queryFactory;
    private $table = 'delegated';
    private $invitesTable = 'invited';

    /**
     * Constructor.
     *
     * @param QueryFactory $queryFactory The query factory
     */
    public function __construct(QueryFactory $queryFactory)
    {
        $this->queryFactory = $queryFactory;
    }

    public function findOneByLogin(int $businessId, Login $login)
    {
        if ($row = $this->queryFactory->newSelect($this->table . ' d')
            ->columns('d.*')
            ->join('INNER', 'users u', 'u.id = d.user')
            ->whereEquals(['u.login' => (string) $login, 'd.business' => $businessId])
            ->fetchOne()
        ) {
            return DelegationData::fromState($row);
        }
        return null;
    }

    public function findDelegate(Login $login)
    {
        return $this->queryFactory->newSelect('users')
            ->columns('id')
            ->whereEquals(['login' => (string) $login])
            ->fetchValue();
    }

    public function listbyBusiness(int $businessId, array $input)
    {
        return $this->queryFactory->newSelect($this->table . ' b', $input)
            ->columns('u.login', 'b.accepted')
            ->join('INNER', 'users u', 'u.id = b.user')
            ->whereEquals(['b.business' => $businessId])
            ->union()
            ->from($this->invitesTable . ' i')
            ->columns('i.login', 'NULL as accepted')
            ->whereEquals(['i.business' => $businessId])
            ->fetchAll();
    }

    public function getOne(int $businessId, string $userId)
    {
        if ($res = $this->findOne($businessId, $userId)) return $res;
        
        throw new InvalidArgumentException('Delegation not found', 4);
    }

    public function findOne(int $businessId, string $userId)
    {
        if ($row = $this->queryFactory->newSelect($this->table)
                ->columns('*')
                ->whereEquals(['business' => $businessId, 'user' => $userId])
                ->fetchOne()
        ) {
            return DelegationData::fromState($row);
        }
    }
    public function save(DelegationData $data)
    {
        if ($data->isNew()) {
            $this->queryFactory->newInsert($this->table, $data->getState())
                ->perform();
            return;
        }
        $this->queryFactory->newUpdate($this->table, ['accepted' => $data->accepted()])
            ->whereEquals(['user' => $data->userId(), 'business' => $data->businessId()])
            ->perform();
    }

    public function delete(DelegationData $data)
    {
        $this->queryFactory->newDelete($this->table)
            ->whereEquals(['business' => $data->businessId(), 'user' => $data->userId()])
            ->perform();
    }

    public function delegateExists(Login $login)
    {
        return (bool) $this->queryFactory->newSelect('users')
            ->column('COUNT(*)')
            ->whereEquals(['login' => (string) $login])
            ->fetchValue();
    }

    public function saveInvitation(int $businessId, string $login)
    {
        $this->queryFactory->newInsert($this->invitesTable, ['login' => $login, 'business' => $businessId])
            ->perform();
    }

    public function listInvitationsByLogin(string $login)
    {
        return $this->queryFactory->newSelect($this->invitesTable)
            ->whereEquals(['login' => $login])
            ->columns('*')
            ->fetchAll();
    }

    public function deleteInvitationsByLogin(string $login)
    {
        $this->queryFactory->newDelete($this->invitesTable)
            ->whereEquals(['login' => $login])
            ->perform();
    }

    public function deleteInvitation(int $businessId, string $login)
    {
        $this->queryFactory->newDelete($this->invitesTable)
            ->whereEquals(['login' => $login, 'business' => $businessId])
            ->perform();
    }
}
