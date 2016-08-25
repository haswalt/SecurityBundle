<?php

namespace Haswalt\SecurityBundle\Event;

use Symfony\Component\EventDispatcher\Event;
use Haswalt\SecurityBundle\Entity\User;

class UserEvent extends Event
{
    protected $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function getUser()
    {
        return $this->user;
    }
}
