<?php

namespace RabbleAndRouser\Wall;

use RabbleAndRouser\Db\DbAwareInterface;
use RabbleAndRouser\Db\DbUsingTrait;
use RabbleAndRouser\Wall\Post\Db\Hydrator;

/**
 * Class Service
 * @package RabbleAndRouser\Wall
 */
class Service implements DbAwareInterface
{
    use DbUsingTrait;

    /**
     * @param Post $post
     */
    public function create(Post $post)
    {
        $this->getDb()->insert('post', array(
            'title' => $post->getTitle(),
            'content' => $post->getContent(),
            'author_id' => $post->getAuthorId(),
            'created' => time(),
        ));
    }

    /**
     * @return mixed
     */
    public function retrieveAll()
    {
        $rows = $this->getDb()->fetchAll('
            SELECT
                post_id,
                author_id,
                title,
                content
            FROM
                post
            ORDER BY
                created DESC
        ');

        $rows = is_array($rows) ? $rows : array();

        $hydrator = new Hydrator();

        $posts = array_reduce($rows, function ($carry, $row) use ($hydrator) {
            $carry[] = $hydrator->hydrate(new Post(), $row);
            return $carry;
        }, array());

        return $posts;
    }

    /**
     * @param $id
     * @return Post
     */
    public function retrieve($id)
    {
        $post = new Post();
        return $post;
    }

    /**
     * @param $id
     */
    public function delete($id)
    {
    }

    /**
     * @param $id
     * @param Post $post
     */
    public function update($id, Post $post)
    {
    }
} 