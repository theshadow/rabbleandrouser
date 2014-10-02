<?php
/**
 * Created by IntelliJ IDEA.
 * User: xanderguzman
 * Date: 10/1/14
 * Time: 7:53 PM
 */

namespace RabbleAndRouser;

use RabbleAndRouser\Model\HydratableInterface;

/**
 * Interface HydratorInterface
 * @package RabbleAndRouser
 */
interface HydratorInterface
{
    /**
     * @param HydratableInterface $object
     * @param $data
     * @return mixed
     */
    public function hydrate(HydratableInterface $object, $data);

    /**
     * @param HydratableInterface $object
     * @return mixed
     */
    public function extract(HydratableInterface $object);
} 