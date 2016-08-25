<?php

namespace Haswalt\SecurityBundle\Security\EntryPoint;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use OAuth2\OAuth2;
use OAuth2\OAuth2AuthenticateException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\EntryPoint\AuthenticationEntryPointInterface;

class OAuthEntryPoint implements AuthenticationEntryPointInterface
{
    protected $oauthService;

    public function __construct(OAuth2 $oauthService)
    {
        $this->oauthService = $oauthService;
    }

    public function start(Request $request, AuthenticationException $authException = null)
    {
        $exception = new OAuth2AuthenticateException(
            OAuth2::HTTP_UNAUTHORIZED,
            OAuth2::TOKEN_TYPE_BEARER,
            $this->oauthService->getVariable(OAuth2::CONFIG_WWW_REALM),
            'access_denied',
            'OAuth2 authentication required'
        );

        return $exception->getHttpResponse();
    }
}
