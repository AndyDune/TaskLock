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


namespace AndyDuneTest\TaskLock;

use AndyDune\TaskLock\Adapter\Mongo;
use AndyDune\TaskLock\Collection;
use AndyDune\TaskLock\Instance;
use PHPUnit\Framework\TestCase;


class UsingMongoAdapterTest extends TestCase
{
    public function testEmpty()
    {
        $mongo = new \MongoDB\Client();
        $collectionDb = $mongo->selectDatabase('test')->selectCollection('test');
        $adapter = new Mongo();
        $adapter->setCollection($collectionDb);

        $collection = new Collection($adapter);

        $name = 'task_test';

        $instance = $collection->getInstance($name);
        $this->assertInstanceOf(Instance::class, $instance);

        $instance->lock();
        $this->assertFalse($instance->isReady());

        $instance->unlock();
        $this->assertTrue($instance->isReady());

        $instance->lock();
        $this->assertFalse($instance->isReady());

        $instance->unlock(10);
        $this->assertFalse($instance->isReady());

        $collectionDb->findOneAndUpdate(['name' => $name], ['$set' => ['datetime_next' => time()]]);
        $this->assertTrue($instance->isReady());


        $instance->lock();

        $instance = $collection->getInstance($name);
        $this->assertFalse($instance->isReady());

        $collectionDb->findOneAndUpdate(['name' => $name], ['$set' => ['datetime_free' => time() - $instance->getMaxTimeFotTaskExecution()]]);

        $this->assertTrue($instance->isReady());
    }

    public function testTaskAssembler()
    {
        $mongo = new \MongoDB\Client();
        $collectionDb = $mongo->selectDatabase('test')->selectCollection('test');
        $adapter = new Mongo();
        $adapter->setCollection($collectionDb);

        $collection = new Collection($adapter);

        $name = 'task_test';

        $instance = $collection->getInstance($name);

    }
}