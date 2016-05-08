<?php

namespace Phalcon2Rest\Components\Oauth2;

use League\OAuth2\Server\Entities\AccessTokenEntityInterface;
use League\OAuth2\Server\Entities\RefreshTokenEntityInterface;
use Psr\Http\Message\ResponseInterface;

class GenerateResult implements \League\OAuth2\Server\ResponseTypes\ResponseTypeInterface {

    public function setAccessToken(AccessTokenEntityInterface $accessToken)
    {
        // TODO: Implement setAccessToken() method.
    }

    public function setRefreshToken(RefreshTokenEntityInterface $refreshToken)
    {
        // TODO: Implement setRefreshToken() method.
    }

    public function generateHttpResponse(ResponseInterface $response)
    {
        // TODO: Implement generateHttpResponse() method.
    }
}