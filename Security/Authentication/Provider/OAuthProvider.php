<?php

namespace Haswalt\SecurityBundle\Security\Authentication\Provider;

use OAuth2\OAuth2;
use OAuth2\OAuth2ServerException;
use OAuth2\OAuth2AuthenticateException;
use Symfony\Component\Security\Core\Authentication\Provider\AuthenticationProviderInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AccountStatusException;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Haswalt\SecurityBundle\Security\Authentication\Token\OAuthToken;

class OAuthProvider implements AuthenticationProviderInterface
{
    protected $userProvider;

    protected $oauthService;

    protected $userChecker;

    public function __construct(UserProviderInterface $userProvider, OAuth2 $oauthService, UserCheckerInterface $userChecker)
    {
        $this->userProvider = $userProvider;
        $this->oauthService = $oauthService;
        $this->userChecker = $userChecker;
    }

    public function authenticate(TokenInterface $token)
    {
        try {
            $tokenString = $token->getToken();

            if ($accessToken = $this->oauthService->verifyAccessToken($tokenString)) {
                $scope = $accessToken->getScope();
                $user = $accessToken->getUser();

                if (null !== $user) {
                    try {
                        $this->userChecker->checkPreAuth($user);
                    } catch (AccountStatusException $e) {
                        throw new OAuth2AuthenticateException(OAuth2::HTTP_UNAUTHORIZED,
                            OAuth2::TOKEN_TYPE_BEARER,
                            $this->oauthService->getVariable(OAuth2::CONFIG_WWW_REALM),
                            'access_denied',
                            $e->getMessage()
                        );
                    }

                    $token->setUser($user);
                }

                $roles = (null !== $user) ? $user->getRoles() : [];

                if (!empty($scope)) {
                    foreach (explode(' ', $scope) as $role) {
                        $roles[] = 'ROLE_'.strtoupper($role);
                    }
                }

                $roles = array_unique($roles, SORT_REGULAR);

                $token = new OAuthToken($roles);
                $token->setAuthenticated(true);
                $token->setToken($tokenString);

                if (null !== $user) {
                    try {
                        $this->userChecker->checkPostAuth($user);
                    } catch (AccountStatusException $e) {
                        throw new OAuth2AuthenticateException(OAuth2::HTTP_UNAUTHORIZED,
                            OAuth2::TOKEN_TYPE_BEARER,
                            $this->serverService->getVariable(OAuth2::CONFIG_WWW_REALM),
                            'access_denied',
                            $e->getMessage()
                        );
                    }

                    $token->setUser($user);
                }

                return $token;
            }
        } catch (OAuth2ServerException $e) {
            throw new AuthenticationException('OAuth2 authentication failed', 0, $e);
        }

        throw new AuthenticationException('OAuth2 authentication failed');
    }

    public function supports(TokenInterface $token)
    {
        return $token instanceof OAuthToken;
    }
}
