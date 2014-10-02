<?php
/**
 * Created by IntelliJ IDEA.
 * User: xanderguzman
 * Date: 10/1/14
 * Time: 6:54 PM
 */

namespace RabbleAndRouser\User;

use Doctrine\DBAL\Connection;
use RabbleAndRouser\Db\DbAwareInterface;
use RabbleAndRouser\Db\DbUsingTrait;
use RabbleAndRouser\User;
use RabbleAndRouser\User\Db\Hydrator;

/**
 * Class Service
 * @package RabbleAndRouser\User
 */
class Service implements DbAwareInterface
{
    use DbUsingTrait;

    /**
     * @param User $user
     * @param $password
     */
    public function create(User $user, $password)
    {
        $this->getDb()->insert('user', array(
            'username' => $user->getUsername(),
            'email' => $user->getEmail(),
            'password_hash' => $password,
        ));
    }

    /**
     * @return array
     */
    public function retrieveAll()
    {
        return array();
    }

    /**
     * @param $id
     * @return User
     */
    public function retrieve($id)
    {
        $user = new User();
        return $user;
    }

    /**
     * @param array $ids
     * @return array|mixed
     */
    public function retrieveUsersById(array $ids)
    {
        if (count($ids) === 0) {
            return array();
        }

        $ids = array_map(function ($id) {
            return intval($id);
        }, $ids);

        $statement = $this->getDb()->executeQuery('
            SELECT
                user_id,
                username,
                email
            FROM
                user
            WHERE user_id IN (?)
        ', array($ids), array(Connection::PARAM_INT_ARRAY));

        $rows = $statement->fetchAll();

        if (!is_array($rows) || count($rows) === 0) {
            return array();
        }

        $hydrator = new Hydrator();

        $users = array_reduce($rows, function ($carry, $row) use ($hydrator) {
            $carry[] = $hydrator->hydrate(new User(), $row);
            return $carry;
        }, array());

        return $users;
    }

    /**
     * @param $email
     * @return mixed
     */
    public function retrieveByEmail($email)
    {
        $row = $this->getDb()->fetchAssoc('
            SELECT
                user_id,
                username,
                email
            FROM
                user
            WHERE
                email = ?
        ', array($email));


        if (!is_array($row)) {
            return null;
        }

        $hydrator = new Hydrator();
        $user = $hydrator->hydrate(new User(), $row);

        return $user;
    }

    /**
     * @param $username
     * @return mixed
     */
    public function retrieveByUsername($username)
    {
        $row = $this->getDb()->fetchAssoc('
            SELECT
                user_id,
                username,
                email
            FROM
                user
            WHERE
                username = ?
        ', array($username));

        if (!is_array($row)) {
            return null;
        }

        $hydrator = new Hydrator();
        $user = $hydrator->hydrate(new User(), $row);

        return $user;
    }

    /**
     * @param $id
     */
    public function delete($id)
    {
    }

    /**
     * @param $id
     * @param User $user
     */
    public function update($id, User $user)
    {
    }
}