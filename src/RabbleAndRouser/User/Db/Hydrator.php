<?php
/**
 * Created by IntelliJ IDEA.
 * User: xanderguzman
 * Date: 10/1/14
 * Time: 9:22 PM
 */

namespace RabbleAndRouser\User\Db;

use RabbleAndRouser\HydratorInterface;
use RabbleAndRouser\Model\HydratableInterface;
use RabbleAndRouser\User;

/**
 * Class Hydrator
 * @package RabbleAndRouser\User\Db
 */
class Hydrator implements HydratorInterface
{
    /**
     * @param HydratableInterface $object
     * @param $data
     * @return mixed
     */
    public function hydrate(HydratableInterface $object, $data)
    {
        /** @var User $model */
        $model = clone $object;
        $model->setId($data['user_id'])
            ->setUsername($data['username'])
            ->setEmail($data['email'])
            ->setWebsite($data['website']);

        return $model;
    }

    /**
     * @param HydratableInterface $object
     * @return mixed
     */
    public function extract(HydratableInterface $object)
    {
        /** @var User $object */
        return array(
            'user_id' => $object->getId(),
            'username' => $object->getUsername(),
            'email' => $object->getEmail(),
            'website' => $object->getWebsite(),
        );
    }

} 