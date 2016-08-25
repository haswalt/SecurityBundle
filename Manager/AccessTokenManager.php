<?php

namespace Haswalt\SecurityBundle\Manager;

use Haswalt\SecurityBundle\Entity\AccessToken;

class AccessTokenManager extends AbstractManager
{
    public function createToken()
    {
        return new AccessToken();
    }

    public function findTokenByToken($token)
    {
        $repository = $this->em->getRepository('HaswaltSecurityBundle:AccessToken');

        return $repository->findOneByToken($token);
    }

    public function deleteExpired()
    {
        $repository = $this->em->getRepository('HaswaltSecurityBundle:AccessToken');
        $qb = $repository->createQueryBuilder('t');
        $qb
            ->delete()
            ->where('t.expiresAt < ?1')
            ->setParameters(array(1 => time()));

        return $qb->getQuery()->execute();
    }
}
