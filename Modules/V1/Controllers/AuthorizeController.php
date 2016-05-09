<?php
namespace Phalcon2Rest\Modules\V1\Controllers;

use League\OAuth2\Server\AuthorizationServer;
use Phalcon2Rest\Components\Oauth2\Entities\UserEntity;
use Phalcon2Rest\Components\Oauth2\Request;
use Phalcon2Rest\Components\Oauth2\Response;
use Phalcon2Rest\Exceptions\HttpException;
use League\OAuth2\Server\Exception\OAuthServerException;
use Phalcon2Rest\Models\Users;

class AuthorizeController extends RestController {

    public function post() {
        /** @var AuthorizationServer $server */
        $server = $this->di->get('authorizationServer');
        $allowedResponseTypes = ['code', 'token'];
        $error = null;
        $result = [];
        $response_type = $this->request->getPost('response_type');
        $request = new Request($this->request);
        $response = new Response($this->response);
        switch($response_type) {
            case 'code':
                try {
                    $authRequest = $server->validateAuthorizationRequest($request);

                    // The auth request object can be serialized and saved into a user's session.
                    // You will probably want to redirect the user at this point to a login endpoint.

                    // Assuming user ID 1 has been logged-in...
                    $user = Users::findFirst(['id' => 1])->toArray();

                    // Once the user has logged in set the user on the AuthorizationRequest
                    $authRequest->setUser(new UserEntity($user)); // an instance of UserEntityInterface

                    // At this point you should redirect the user to an authorization page.
                    // This form will ask the user to approve the client and the scopes requested.

                    // Once the user has approved or denied the client update the status
                    // (true = approved, false = denied)
                    $authRequest->setAuthorizationApproved(true);

                    // Return the HTTP redirect response
                    $url = $server->completeAuthorizationRequest($authRequest, $response)->getHeader('Location');
                    $this->response->redirect($url);
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
            case 'token':
                try {
                    // Validate the HTTP request and return an AuthorizationRequest object.
                    $authRequest = $server->validateAuthorizationRequest($request);
                    // The auth request object can be serialized and saved into a user's session.
                    // You will probably want to redirect the user at this point to a login endpoint.

                    // for simplicity we assume that user with id 1 has been logged-in
                    $user = Users::findFirst(['id' => 1])->toArray();

                    // Once the user has logged in set the user on the AuthorizationRequest
                    $authRequest->setUser(new UserEntity($user)); // an instance of UserEntityInterface

                    // At this point you should redirect the user to an authorization page.
                    // This form will ask the user to approve the client and the scopes requested.

                    // Once the user has approved or denied the client update the status
                    // (true = approved, false = denied)
                    $authRequest->setAuthorizationApproved(true);

                    // Return the HTTP redirect response
                    $redirectUrl = $server->completeAuthorizationRequest($authRequest, $response)->getHeader('Location');
                    $this->response->redirect($redirectUrl);
                } catch (OAuthServerException $exception) {
                    switch ($exception->getCode()) {
                        case 9:
                            $url = $exception->generateHttpResponse($response)->getHeader('Location');
                            $this->response->redirect($url);
                            break;
                        default:
                            $error = [
                                $exception->getMessage(),
                                $exception->getHttpStatusCode(),
                                null,
                                [
                                    'dev' => $exception->getHint(),
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
                    "The response type is not allowed {$response_type}",
                    400,
                    [
                        'dev' => "Allowed response types are: " . implode(', ', $allowedResponseTypes),
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