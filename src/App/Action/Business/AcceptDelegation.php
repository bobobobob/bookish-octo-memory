<?php

namespace App\Action\Business;

use App\Responder\Responder;
use Domain\Business\Service\Delegator;
use Domain\Common\Model\AuthenticatedUser;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class AcceptDelegation
{
    private $responder;

    private $delegator;

    private $auth;

    public function __construct(Responder $responder, Delegator $delegator, AuthenticatedUser $auth)
    {
        $this->responder = $responder;
        $this->delegator = $delegator;
        $this->auth = $auth;
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        return $this->responder->ok(
            $response,
            $this->delegator->accept($this->auth->userId(), (int) $args['bid'])
        );
    }
}
