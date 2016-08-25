<?php

namespace Haswalt\SecurityBundle\Manager;

use Doctrine\ORM\EntityManager;

class AbstractManager
{
    protected $em;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    public function updateToken($token)
    {
        $this->em->persist($token);
        $this->em->flush();
    }

    public function deleteToken($token)
    {
        $this->em->remove($token);
        $this->em->flush();
    }
}
