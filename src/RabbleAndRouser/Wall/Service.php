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

    const SORT_ASC = 'asc';
    const SORT_DESC = 'desc';

    public static $VALID_SORT_VALUES = array(
        self::SORT_ASC,
        self::SORT_DESC,
    );

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
     * @param string $sort asc|desc only valid values
     * @return mixed
     * @throws \InvalidArgumentException
     */
    public function retrieveAll($sort = self::SORT_DESC)
    {
        if (!in_array($sort, static::$VALID_SORT_VALUES)) {
            throw new \InvalidArgumentException('Sort must be either asc or desc');
        }

        $rows = $this->getDb()->fetchAll('
            SELECT
                post_id,
                author_id,
                title,
                content,
                created
            FROM
                post
            ORDER BY
                created ' . strtoupper($sort));

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