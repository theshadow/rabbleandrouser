<?php
/**
 * Created by IntelliJ IDEA.
 * User: xanderguzman
 * Date: 10/1/14
 * Time: 7:00 PM
 */

namespace RabbleAndRouser\Db;

/**
 * Class DbUsingTrait
 * @package RabbleAndRouser\Db
 */
trait DbUsingTrait
{
    /**
     * @var
     */
    protected $db;

    /**
     * @return mixed
     */
    public function getDb()
    {
        return $this->db;
    }

    /**
     * @param mixed $db
     * @return static
     */
    public function setDb($db)
    {
        $this->db = $db;
        return $this;
    }
}