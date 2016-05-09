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
        $allowedGrandTypes = ['client_credentials', 'password'];
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
                    switch($exception->getCode()) {
                        case 6:
                            $error = [
                                'Wrong credentials',
                                401,
                                [
                                    'dev' => $exception->getMessage(),
                                    'internalCode' => 'P1007',
                                    'more' => ''
                                ]
                            ];
                            break;
                        default:
                            $error = [
                                'Unknown error',
                                500,
                                [
                                    'dev' => $exception->getMessage(),
                                    'internalCode' => 'P1006',
                                    'more' => ''
                                ]
                            ];
                    }

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
                    switch ($exception->getCode()) {
                        case 2:
                            $error = [
                                "Missing parameters",
                                400,
                                [
                                    'dev' => 'client_id, client_secret and scope must be sent as well',
                                    'internalCode' => 'P1002',
                                    'more' => ''
                                ]
                            ];
                            break;
                        case 4:
                            $error = [
                                'Wrong credentials',
                                401,
                                [
                                    'dev' => $exception->getMessage(),
                                    'internalCode' => 'P1007',
                                    'more' => ''
                                ]
                            ];
                            break;
                        default:
                            $error = [
                                'Unknown error',
                                500,
                                [
                                    'dev' => $exception->getMessage(),
                                    'internalCode' => 'P1004',
                                    'more' => ''
                                ]
                            ];
                    }
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
                    switch ($exception->getCode()) {
                        default:
                            $error = [
                                'Unknown error',
                                500,
                                [
                                    'dev' => $exception->getMessage(),
                                    'internalCode' => 'P1004',
                                    'more' => ''
                                ]
                            ];
                    }
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