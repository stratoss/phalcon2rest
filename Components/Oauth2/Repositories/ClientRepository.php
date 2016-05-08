<?php
/**
 * @author      Alex Bilbie <hello@alexbilbie.com>
 * @copyright   Copyright (c) Alex Bilbie
 * @license     http://mit-license.org/
 *
 * @link        https://github.com/thephpleague/oauth2-server
 */

namespace Phalcon2Rest\Components\Oauth2\Repositories;

use League\OAuth2\Server\Repositories\ClientRepositoryInterface;
use Phalcon\Security;
use Phalcon2Rest\Components\Oauth2\Entities\ClientEntity;
use Phalcon\Di\FactoryDefault as Di;
use Phalcon2Rest\Models\Clients;

class ClientRepository implements ClientRepositoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function getClientEntity($clientIdentifier, $grantType, $clientSecret = null, $mustValidateSecret = true)
    {
        $di = new Di();
        /** @var Security $security */
        $security = $di->getShared('security');
        $client = Clients::query()
            ->where("id = :id:")
            ->bind([
                'id' => $clientIdentifier
            ])
            ->limit(1)
            ->execute()
            ->toArray();
        $correctDetails = false;
        if (count($client) === 1) {
            $client = current($client);
            if ($mustValidateSecret) {

                if ($security->checkHash($clientSecret, $client['secret'])) {
                    $correctDetails = true;
                } else {
                    $security->hash(rand());

                }
            } else {
                $correctDetails = true;
            }
        } else {
            // prevent timing attacks
            $security->hash(rand());
        }

        if ($correctDetails) {
            $clientEntity = new ClientEntity();
            $clientEntity->setIdentifier($clientIdentifier);
            $clientEntity->setName($client['name']);
            $clientEntity->setRedirectUri($client['redirect_url']);
            return $clientEntity;
        }
        return null;
    }
}