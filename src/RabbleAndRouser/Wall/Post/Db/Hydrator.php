<?php
/**
 * Created by IntelliJ IDEA.
 * User: xanderguzman
 * Date: 10/1/14
 * Time: 7:52 PM
 */

namespace RabbleAndRouser\Wall\Post\Db;

use RabbleAndRouser\HydratorInterface;
use RabbleAndRouser\Model\HydratableInterface;
use RabbleAndRouser\Wall\Post;

/**
 * Class Hydrator
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
        /** @var Post $model */
        $model = clone $object;
        $model->setId($data['post_id'])
            ->setAuthorId($data['author_id'])
            ->setTitle($data['title'])
            ->setContent($data['content'])
            ->setCreated($data['created']);

        return $model;
    }

    /**
     * @param HydratableInterface $object
     * @return mixed
     */
    public function extract(HydratableInterface $object)
    {
        /** @var Post $object */
        $data = array(
            'post_id' => $object->getId(),
            'author_id' => $object->getAuthorId(),
            'title' => $object->getTItle(),
            'content' => $object->getContent(),
            'created' => $object->getCreated(),
        );

        return $data;
    }
}