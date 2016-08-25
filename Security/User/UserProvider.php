<?php

namespace Haswalt\SecurityBundle\Security\User;

use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Doctrine\ORM\EntityManager;
use Haswalt\SecurityBundle\Entity\User;

class UserProvider implements UserProviderInterface
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @param  string $username
     * @return User
     */
    public function loadUserByUsername($username)
    {
        $user = $this->entityManager
            ->getRepository('HaswaltSecurityBundle:User')
            ->findOneByUsername($username);

        if (!$user) {
            throw new UsernameNotFoundException(
                sprintf('Username "%s" does not exist.', $username)
            );
        }

        return $user;
    }

    /**
     * @param  UserInterface $user
     * @return User
     */
    public function refreshUser(UserInterface $user)
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(
                sprintf('Instances of "%s" are not supported.', get_class($user))
            );
        }

        return $this->loadByUsername($user->getUsername());
    }

    /**
     * @param  string $class
     * @return boolean
     */
    public function supportsClass($class)
    {
        return $class === 'Haswalt\SecurityBundle\Entity\User';
    }
}
