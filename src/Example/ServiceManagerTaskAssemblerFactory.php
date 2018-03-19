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

namespace AndyDune\TaskLock\Example;
namespace AndyDune\TaskLock\Example;
use AndyDune\TaskLock\TaskAssembler;
use Psr\Container\ContainerInterface;
use AndyDune\TaskLock\Collection;
use AndyDune\TaskLock\Adapter\Mongo;


class ServiceManagerTaskAssemblerFactory
{
    public function __invoke(ContainerInterface $container)
    {
        /** @var \MongoDB\Database $connection */
        $connection = $container->get('mongo_db_connection');
        $collectionDb = $connection->selectCollection('rzn_lock_task');
        $adapter = new Mongo();
        $adapter->setCollection($collectionDb);
        $collection = Collection($adapter);

        $taskAssembler = new TaskAssembler($collection);
        //$taskAssembler->addInitializer(); // add initializer for interface drive injections
        return $taskAssembler;
    }
}