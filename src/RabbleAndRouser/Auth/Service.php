<?php
/**
 * Created by IntelliJ IDEA.
 * User: xanderguzman
 * Date: 10/1/14
 * Time: 7:10 PM
 */

namespace RabbleAndRouser\Auth;

use RabbleAndRouser\Db\DbAwareInterface;
use RabbleAndRouser\Db\DbUsingTrait;

/**
 * Class Service
 * @package RabbleAndRouser\Auth
 */
class Service implements DbAwareInterface
{
    use DbUsingTrait;

    /**
     * Defines the cost for the bcrypt algorithm
     *
     * @todo turn this into a configuration option.
     */
    const BCRYPT_COST = 11;

    /**
     * @param string $email
     * @param string $password
     * @return bool
     */
    public function authenticate($email, $password)
    {
        $result = $this->getDb()->fetchAssoc("SELECT password_hash FROM user WHERE email = ?", array($email));

        if (!is_array($result) || !isset($result['password_hash'])
            || password_verify($password, $result['password_hash']) === false) {
            return false;
        }

        return true;
    }

    /**
     * @param string $value
     * @return bool|string
     */
    public function hashPassword($value)
    {
        return password_hash($value, PASSWORD_BCRYPT, array('cost' => static::BCRYPT_COST));
    }
} 