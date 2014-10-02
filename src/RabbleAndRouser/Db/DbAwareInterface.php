<?php
/**
 * Created by IntelliJ IDEA.
 * User: xanderguzman
 * Date: 10/1/14
 * Time: 7:02 PM
 */

namespace RabbleAndRouser\Db;

/**
 * Interface DbAwareInterface
 * @package RabbleAndRouser\Db
 */
interface DbAwareInterface
{
    /**
     * @param $db
     * @return mixed
     */
    public function setDb($db);
} 