<?php

namespace Haswalt\SecurityBundle\Security\Firewall;

use Symfony\Component\Security\Http\Firewall\ListenerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\AuthenticationManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpFoundation\Response;
use OAuth2\OAuth2;
use Haswalt\SecurityBundle\Security\Authentication\Token\OAuthToken;

class OAuthListener implements ListenerInterface
{
    protected $tokenStorage;

    protected $authenticationManager;

    protected $oauthService;

    public function __construct(TokenStorageInterface $tokenStorage, AuthenticationManagerInterface $authenticationManager, OAuth2 $oauthService)
    {
        $this->tokenStorage = $tokenStorage;
        $this->authenticationManager = $authenticationManager;
        $this->oauthService = $oauthService;
    }

    public function handle(GetResponseEvent $event)
    {
        $oauthToken = $this->oauthService->getBearerToken($event->getRequest(), true);
        if (null === $oauthToken) {
            return;
        }

        $token = new OAuthToken();
        $token->setToken($oauthToken);

        try {
            $authToken = $this->authenticationManager->authenticate($token);

            if ($authToken instanceof TokenInterface) {
                return $this->tokenStorage->setToken($authToken);
            }

            if ($authToken instanceof Response) {
                return $event->setResponse($authToken);
            }
        } catch(AuthenticationException $e) {
            $p = $e->getPrevious();
            if (null !== $p) {
                $event->setResponse($p->getHttpResponse());
            }
        }
    }
}
