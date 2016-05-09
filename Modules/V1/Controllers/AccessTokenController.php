<?php
namespace Phalcon2Rest\Modules\V1\Controllers;

use League\OAuth2\Server\AuthorizationServer;
use Phalcon2Rest\Components\Oauth2\Request;
use Phalcon2Rest\Components\Oauth2\Response;
use Phalcon2Rest\Exceptions\HttpException;
use League\OAuth2\Server\Exception\OAuthServerException;

class AccessTokenController extends RestController {

    public function post() {
        /** @var AuthorizationServer $server */
        $server = $this->di->get('authorizationServer');
        $allowedGrandTypes = ['client_credentials', 'password', 'refresh_token'];
        $error = null;
        $result = [];
        $grant_type = $this->request->getPost('grant_type');
        $request = new Request($this->request);
        $response = new Response($this->response);
        switch($grant_type) {
            case 'password':
                try {
                    // Try to respond to the request
                    $server->respondToAccessTokenRequest($request, $response);
                    $result = $response->getToken();
                } catch (OAuthServerException $exception) {
                     $error = [
                         $exception->getMessage(),
                         $exception->getHttpStatusCode(),
                         null,
                         [
                             'dev' => $exception->getHint(),
                         ]
                     ];
                } catch (\Exception $exception) {
                    $error = [
                        'Unknown error',
                        500,
                        [
                            'dev' => $exception->getMessage(),
                            'internalCode' => 'P1005',
                            'more' => ''
                        ]
                    ];
                }
                break;
            case 'client_credentials':
                try {
                    // Try to respond to the request
                    $server->respondToAccessTokenRequest($request, $response);
                    $result = $response->getToken();
                } catch (OAuthServerException $exception) {
                    $error = [
                        $exception->getMessage(),
                        $exception->getHttpStatusCode(),
                        null,
                        [
                            'dev' => $exception->getHint(),
                        ]
                    ];
                } catch (\Exception $exception) {
                    $error = [
                        'Unknown error',
                        500,
                        [
                            'dev' => $exception->getMessage(),
                            'internalCode' => 'P1003',
                            'more' => ''
                        ]
                    ];
                }
                break;
            case 'refresh_token':
                try {
                    // Try to respond to the request
                    $server->respondToAccessTokenRequest($request, $response);
                    $result = $response->getToken();
                } catch (OAuthServerException $exception) {
                    $error = [
                        $exception->getMessage(),
                        $exception->getHttpStatusCode(),
                        null,
                        [
                            'dev' => $exception->getHint(),
                        ]
                    ];
                } catch (\Exception $exception) {
                    $error = [
                        'Unknown error',
                        500,
                        [
                            'dev' => $exception->getMessage(),
                            'internalCode' => 'P1003',
                            'more' => ''
                        ]
                    ];
                }
                
                break;
            default:
                $error = [
                    "The grant type is not allowed {$grant_type}",
                    400,
                    [
                        'dev' => "Allowed grant types are: " . implode(', ', $allowedGrandTypes),
                        'internalCode' => 'P1001',
                        'more' => ''
                    ]
                ];
        }
        if ($error !== null && is_array($error) && count($error) === 3) {
            throw new HttpException(
                $error[0],
                $error[1],
                null,
                $error[2]
            );
        }
        return json_decode($result, true);
    }
}