<?php
/**
 * @author      Alex Bilbie <hello@alexbilbie.com>
 * @copyright   Copyright (c) Alex Bilbie
 * @license     http://mit-license.org/
 *
 * @link        https://github.com/thephpleague/oauth2-server
 */

namespace Phalcon2Rest\Components\Oauth2\Repositories;

use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Repositories\UserRepositoryInterface;
use Phalcon\Di\FactoryDefault as Di;
use Phalcon\Security;
use Phalcon2Rest\Components\Oauth2\Entities\UserEntity;
use Phalcon2Rest\Models\Users;

class UserRepository implements UserRepositoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function getUserEntityByUserCredentials(
        $username,
        $password,
        $grantType,
        ClientEntityInterface $clientEntity
    ) {
        $di = new Di();
        /** @var Security $security */
        $security = $di->getShared('security');
        $user = Users::query()
            ->where("username = :username:")
            ->bind([
                'username' => $username
            ])
            ->limit(1)
            ->execute()
            ->toArray();
        $correctDetails = false;
        if (count($user) === 1) {
            $user = current($user);
            if ($security->checkHash($password, $user['password'])) {
                $correctDetails = true;
            } else {
                $security->hash(rand());
            }
        } else {
            // prevent timing attacks
            $security->hash(rand());
        }
        if ($correctDetails) {
            //$scope = new ScopeEntity();
            //$scope->setIdentifier('email');
            //$scopes[] = $scope;

            return new UserEntity($user);
        }
        return null;
    }
}