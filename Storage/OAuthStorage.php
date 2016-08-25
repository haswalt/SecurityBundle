<?php

namespace Haswalt\SecurityBundle\Storage;

use Haswalt\SecurityBundle\Manager\ClientManager;
use Haswalt\SecurityBundle\Manager\AccessTokenManager;
use Haswalt\SecurityBundle\Manager\RefreshTokenManager;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use OAuth2\OAuth2;
use OAuth2\OAuth2ServerException;
use OAuth2\IOAuth2RefreshTokens;
use OAuth2\IOAuth2GrantUser;
use OAuth2\IOAuth2GrantCode;
use OAuth2\IOAuth2GrantImplicit;
use OAuth2\IOAuth2GrantClient;
use OAuth2\IOAuth2GrantExtension;
use OAuth2\Model\IOAuth2Client;

class OAuthStorage implements IOAuth2RefreshTokens, IOAuth2GrantUser, IOAuth2GrantCode, IOAuth2GrantImplicit, IOAuth2GrantClient, IOAuth2GrantExtension
{
    protected $clientManager;

    protected $accessTokenManager;

    protected $refreshTokenManager;

    protected $userProvider;

    protected $encoderFactory;

    protected $grantExtensions;

    public function __construct(ClientManager $clientManager, accessTokenManager $accessTokenManager, RefreshTokenManager $refreshTokenManager, UserProviderInterface $userProvider, EncoderFactoryInterface $encoderFactory = null)
    {
        $this->clientManager = $clientManager;
        $this->accessTokenManager = $accessTokenManager;
        $this->refreshTokenManager = $refreshTokenManager;
        $this->userProvider = $userProvider;
        $this->encoderFactory = $encoderFactory;

        $this->grantExtensions = [];
    }

    /**
     * {@inheritdoc}
     */
    public function setGrantExtension($uri, GrantExtensionInterface $grantExtension)
    {
        $this->grantExtensions[$uri] = $grantExtension;
    }

    public function getClient($clientId)
    {
        return $this->clientManager->findClientByPublicId($clientId);
    }

    public function checkClientCredentials(IOAuth2Client $client, $client_secret = null)
    {
        return $client->checkSecret($client_secret);
    }

    public function checkClientCredentialsGrant(IOAuth2Client $client, $client_secret)
    {
        return $this->checkClientCredentials($client, $client_secret);
    }

    public function getAccessToken($token)
    {
        return $this->accessTokenManager->findTokenByToken($token);
    }

    public function createAccessToken($tokenString, IOAuth2Client $client, $data, $expires, $scope = null)
    {
        $token = $this->accessTokenManager->createToken();
        $token->setToken($tokenString);
        $token->setClient($client);
        $token->setExpiresAt($expires);
        $token->setScope($scope);

        if (null !== $data) {
            $token->setUser($data);
        }

        $this->accessTokenManager->updateToken($token);

        return $token;
    }

    public function checkRestrictedGrantType(IOAuth2Client $client, $grant_type)
    {
        return in_array($grant_type, $client->getAllowedGrantTypes(), true);
    }

    public function checkUserCredentials(IOAuth2Client $client, $username, $password)
    {
        try {
            $user = $this->userProvider->loadUserByUsername($username);
        } catch (AuthenticationException $e) {
            return false;
        }

        if (null !== $user) {
            $encoder = $this->encoderFactory->getEncoder($user);

            if ($encoder->isPasswordValid($user->getPassword(), $password, $user->getSalt())) {
                return array(
                    'data' => $user,
                );
            }
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function getAuthCode($code)
    {
        return $this->authCodeManager->findAuthCodeByToken($code);
    }

    /**
     * {@inheritdoc}
     */
    public function createAuthCode($code, IOAuth2Client $client, $data, $redirect_uri, $expires, $scope = null)
    {
        $authCode = $this->authCodeManager->createAuthCode();
        $authCode->setToken($code);
        $authCode->setClient($client);
        $authCode->setUser($data);
        $authCode->setRedirectUri($redirect_uri);
        $authCode->setExpiresAt($expires);
        $authCode->setScope($scope);
        $this->authCodeManager->updateAuthCode($authCode);

        return $authCode;
    }

    /**
     * {@inheritdoc}
     */
    public function getRefreshToken($tokenString)
    {
        return $this->refreshTokenManager->findTokenByToken($tokenString);
    }

    /**
     * {@inheritdoc}
     */
    public function createRefreshToken($tokenString, IOAuth2Client $client, $data, $expires, $scope = null)
    {
        $token = $this->refreshTokenManager->createToken();
        $token->setToken($tokenString);
        $token->setClient($client);
        $token->setExpiresAt($expires);
        $token->setScope($scope);

        if (null !== $data) {
            $token->setUser($data);
        }

        $this->refreshTokenManager->updateToken($token);

        return $token;
    }

    /**
     * {@inheritdoc}
     */
    public function unsetRefreshToken($tokenString)
    {
        $token = $this->refreshTokenManager->findTokenByToken($tokenString);

        if (null !== $token) {
            $this->refreshTokenManager->deleteToken($token);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function checkGrantExtension(IOAuth2Client $client, $uri, array $inputData, array $authHeaders)
    {
        if (!isset($this->grantExtensions[$uri])) {
            throw new OAuth2ServerException(OAuth2::HTTP_BAD_REQUEST, OAuth2::ERROR_UNSUPPORTED_GRANT_TYPE);
        }

        $grantExtension = $this->grantExtensions[$uri];

        return $grantExtension->checkGrantExtension($client, $inputData, $authHeaders);
    }

    /**
     * {@inheritdoc}
     */
    public function markAuthCodeAsUsed($code)
    {
        $authCode = $this->authCodeManager->findAuthCodeByToken($code);

        if (null !== $authCode) {
            $this->authCodeManager->deleteAuthCode($authCode);
        }
    }
}
