<?php

namespace Haswalt\SecurityBundle\Entity;

use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\EquatableInterface;

/**
 * User
 */
class User implements UserInterface, EquatableInterface
{
    const ROLE_USER = 'ROLE_USER';
    const ROLE_WORKER = 'ROLE_WORKER';
    const ROLE_EMPLOYER = 'ROLE_EMPLOYER';

    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $username;

    /**
     * @var string
     */
    private $password;

    /**
     * @var array
     */
    private $roles;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $applications;

    /**
     * @var \Haswalt\SecurityBundle\Entity\Profile
     */
    private $profile;

    /**
     * @var string
     */
    private $forgotToken;

    /**
     * @var \DateTime
     */
    private $forgotAt;

    /**
     * Create a new User object
     */
    public function __construct()
    {
        $this->roles = [self::ROLE_USER, self::ROLE_WORKER];
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set username
     *
     * @param string $username
     *
     * @return User
     */
    public function setUsername($username)
    {
        $this->username = $username;

        return $this;
    }

    /**
     * Get username
     *
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * Set password
     *
     * @param string $password
     *
     * @return User
     */
    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Get password
     *
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Set roles
     *
     * @param array $roles
     *
     * @return User
     */
    public function setRoles($roles)
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * Get roles
     *
     * @return array
     */
    public function getRoles()
    {
        return $this->roles;
    }

    public function getSalt()
    {
        return null;
    }

    public function eraseCredentials()
    {

    }

    public function isEqualTo(userInterface $user)
    {
        if (!$user instanceof User) {
            return false;
        }

        if ($this->password !== $user->getPassword()) {
            return false;
        }

        if ($this->username !== $user->getUsername()) {
            return false;
        }

        return true;
    }

    /**
     * Add application
     *
     * @param \Haswalt\JobBundle\Entity\Application $application
     *
     * @return User
     */
    public function addApplication(\Haswalt\JobBundle\Entity\Application $application)
    {
        $this->applications[] = $application;

        return $this;
    }

    /**
     * Remove application
     *
     * @param \Haswalt\JobBundle\Entity\Application $application
     */
    public function removeApplication(\Haswalt\JobBundle\Entity\Application $application)
    {
        $this->applications->removeElement($application);
    }

    /**
     * Get applications
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getApplications()
    {
        return $this->applications;
    }

    /**
     * Set profile
     *
     * @param \Haswalt\SecurityBundle\Entity\Profile $profile
     *
     * @return User
     */
    public function setProfile(\Haswalt\SecurityBundle\Entity\Profile $profile = null)
    {
        $this->profile = $profile;

        return $this;
    }

    /**
     * Get profile
     *
     * @return \Haswalt\SecurityBundle\Entity\Profile
     */
    public function getProfile()
    {
        return $this->profile;
    }

    /**
     * Set forgotToken
     *
     * @param string $forgotToken
     *
     * @return User
     */
    public function setForgotToken($forgotToken)
    {
        $this->forgotToken = $forgotToken;

        return $this;
    }

    /**
     * Get forgotToken
     *
     * @return string
     */
    public function getForgotToken()
    {
        return $this->forgotToken;
    }

    /**
     * Set forgotAt
     *
     * @param \DateTime $forgotAt
     *
     * @return User
     */
    public function setForgotAt($forgotAt)
    {
        $this->forgotAt = $forgotAt;

        return $this;
    }

    /**
     * Get forgotAt
     *
     * @return \DateTime
     */
    public function getForgotAt()
    {
        return $this->forgotAt;
    }

    public function getPrimaryRole()
    {
        return $this->getRoles()[0];
    }
}
