<?php

namespace Haswalt\SecurityBundle\Manager;

use Haswalt\SecurityBundle\Entity\RefreshToken;

class RefreshTokenManager extends AbstractManager
{
    public function createToken()
    {
        return new RefreshToken();
    }

    public function findTokenByToken($token)
    {
        $repository = $this->em->getRepository('HaswaltSecurityBundle:RefreshToken');

        return $repository->findOneByToken($token);
    }

    public function deleteExpired()
    {
        $repository = $this->em->getRepository('HaswaltSecurityBundle:RefreshToken');
        $qb = $repository->createQueryBuilder('t');
        $qb
            ->delete()
            ->where('t.expiresAt < ?1')
            ->setParameters(array(1 => time()));

        return $qb->getQuery()->execute();
    }
}
