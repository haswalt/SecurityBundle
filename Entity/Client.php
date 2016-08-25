<?php

namespace Haswalt\SecurityBundle\Entity;

use OAuth2\Model\IOAuth2Client;

/**
 * Client
 */
class Client implements IOAuth2Client
{
    /**
     * @var string
     */
    private $randomId;

    /**
     * @var array
     */
    private $redirectUris;

    /**
     * @var string
     */
    private $secret;

    /**
     * @var array
     */
    private $allowedGrantTypes;

    /**
     * @var integer
     */
    private $id;

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
     * Set randomId
     *
     * @param string $randomId
     *
     * @return Client
     */
    public function setRandomId($randomId)
    {
        $this->randomId = $randomId;
        $this->allowedGrantTypes = [];

        return $this;
    }

    /**
     * Get randomId
     *
     * @return string
     */
    public function getRandomId()
    {
        return $this->randomId;
    }

    /**
     * Set redirectUris
     *
     * @param array $redirectUris
     *
     * @return Client
     */
    public function setRedirectUris($redirectUris)
    {
        $this->redirectUris = $redirectUris;

        return $this;
    }

    /**
     * Get redirectUris
     *
     * @return array
     */
    public function getRedirectUris()
    {
        return $this->redirectUris;
    }

    /**
     * Set secret
     *
     * @param string $secret
     *
     * @return Client
     */
    public function setSecret($secret)
    {
        $this->secret = $secret;

        return $this;
    }

    /**
     * Get secret
     *
     * @return string
     */
    public function getSecret()
    {
        return $this->secret;
    }

    /**
     * Set allowedGrantTypes
     *
     * @param array $allowedGrantTypes
     *
     * @return Client
     */
    public function setAllowedGrantTypes($allowedGrantTypes)
    {
        $this->allowedGrantTypes = $allowedGrantTypes;

        return $this;
    }

    /**
     * Get allowedGrantTypes
     *
     * @return array
     */
    public function getAllowedGrantTypes()
    {
        return $this->allowedGrantTypes;
    }

    public function getPublicId()
    {
        return $this->randomId;
    }

    public function checkSecret($secret)
    {
        return null === $this->secret || $secret === $this->secret;
    }
}
