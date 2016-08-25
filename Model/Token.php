<?php

namespace Haswalt\SecurityBundle\Model;

abstract class Token
{
    /**
     * Get client id
     *
     * @return integer
     */
    public function getClientId()
    {
        return $this->getClient()->getPublicId();
    }

    /**
     * Get expires time
     *
     * @return integer
     */
    public function getExpiresIn()
    {
        if ($this->getExpiresAt()) {
            return $this->getExpiresAt() - time();
        }

        return PHP_INT_MAX;
    }

    /**
     * Has expired
     *
     * @return boolean
     */
    public function hasExpired()
    {
        if ($this->getExpiresAt()) {
            return time() > $this->getExpiresAt();
        }

        return false;
    }

    public function getData()
    {
        return $this->getUser();
    }
}
