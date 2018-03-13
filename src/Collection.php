<?php
/**
 *
 * PHP version 7.X
 *
 * @package andydune/task-lock
 * @link  https://github.com/AndyDune/TaskLock for the canonical source repository
 * @license   http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Andrey Ryzhov  <info@rznw.ru>
 * @copyright 2018 Andrey Ryzhov
 */


namespace AndyDune\TaskLock;
use AndyDune\TaskLock\Adapter\AdapterInterface;

class Collection
{
    /**
     * @var AdapterInterface
     */
    private $adapter;

    public function __construct(AdapterInterface $adapter)
    {
        $this->adapter = $adapter;
    }

    public function getAdapter()
    {
        return $this->adapter;
    }

    public function getInstance($name)
    {
        return new Instance($name, $this->getAdapter());
    }
}