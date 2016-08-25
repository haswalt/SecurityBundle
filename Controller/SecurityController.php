<?php

namespace Haswalt\SecurityBundle\Controller;

use Haswalt\ApiBundle\Controller\ApiController;
use Symfony\Component\HttpFoundation\Request;
use OAuth2\OAuth2ServerException;

class SecurityController extends ApiController
{
    public function tokenAction(Request $request)
    {
        try {
            $server = $this->get('oauth_server.server');
            return $server->grantAccessToken($request);
        } catch (OAuth2ServerException $e) {
            return $e->getHttpResponse();
        }
    }
}
