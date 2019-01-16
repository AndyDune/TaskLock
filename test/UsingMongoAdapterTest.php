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
use AndyDune\TaskLock\Example\TaskForAdapter;
use AndyDune\TaskLock\TaskAssembler;

use AndyDune\TaskLock\Adapter\Mongo;
use AndyDune\TaskLock\Collection;
use AndyDune\TaskLock\Instance;
use AndyDune\TaskLock\TaskAssemblerException;
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

        $collection->getInstance('chupa')->delete();
        $collection->getInstance(TaskForAdapter::class)->delete();

        $task1 = new class{
            protected $resultHolder;
            public $exception = false;
            public function setResultHolder($res)
            {
                $this->resultHolder = $res;
            }

            public function __invoke()
            {
                if ($this->exception) {
                    throw new \Exception();
                }
                $this->resultHolder->setResult('chupa');
            }
        };

        $init = new class{

            public $classes = [];
            public $results = [];

            public function __invoke($instance)
            {
                $instance->setResultHolder($this);
                $this->classes[] = get_class($instance);
            }

            public function setResult($result)
            {
                $this->results[] = $result;
            }
        };

        $task = new TaskAssembler($collection);
        $task->addInitializer($init);
        $task->add($task1, 'chupa');
        $task->execute();

        $this->assertEquals(1, count($init->results));
        $this->assertEquals(1, count($init->classes));
        $this->assertEquals('chupa', current($init->results));

        $init->results = [];
        $task1->exception = true;

        try {
            $task->execute();
            $this->assertTrue(false);
        } catch (\Exception $e) {

        }
        $this->assertEquals(0, count($init->results));

        $task1->exception = false;

        $task->execute();
        $this->assertEquals(0, count($init->results));

        $collection->getInstance('chupa')->unlock();


        $task->execute();
        $this->assertEquals(1, count($init->results));

        $init->results = [];

        $task->add(TaskForAdapter::class);
        $task->execute(true);
        $this->assertEquals(1, count($init->results));
        $this->assertEquals('chupa', current($init->results));

        $init->results = [];
        $task1->exception = true;

        try {
            $task->execute(true);
            $this->assertTrue(false);
        } catch (\Exception $e) {

        }
        $task1->exception = false;

        $init->results = [];
        $task->execute(true);
        $this->assertEquals(1, count($init->results));
        $this->assertEquals('remedy', current($init->results));

        $collection->getInstance('chupa')->unlock();

        $init->results = [];
        $task->execute();

        $this->assertEquals(2, count($init->results));
        $this->assertEquals('chupa', array_shift($init->results));
        $this->assertEquals('remedy', array_shift($init->results));

        $collection->getInstance('chupa')->delete();
        $collection->getInstance(TaskForAdapter::class)->delete();



        $task1 = new class{
            protected $resultHolder;
            public $exception = false;
            public function setResultHolder($res)
            {
                $this->resultHolder = $res;
            }

            public function __invoke()
            {
                $this->resultHolder->setResult('chupa');
                $ex = new TaskAssemblerException();
                $ex->setNextExecutionDelay(20);
                throw $ex;
            }
        };

        $task = new TaskAssembler($collection);
        $task->addInitializer($init);
        $task->add($task1, 'chupa');

        $init->results = [];
        $task->execute();
        $this->assertEquals(1, count($init->results));


        $init->results = [];
        $task->execute();
        $this->assertEquals(0, count($init->results));

        $collectionDb->updateOne(['name' => 'chupa'], ['$set' => ['datetime_next' => time()]]);

        $init->results = [];
        $task->execute();
        $this->assertEquals(1, count($init->results));


        $collection->getInstance('chupa')->delete();

    }

    public function testTaskAssemblerException()
    {
        $mongo = new \MongoDB\Client();
        $collectionDb = $mongo->selectDatabase('test')->selectCollection('test');
        $adapter = new Mongo();
        $adapter->setCollection($collectionDb);

        $collection = new Collection($adapter);

        $collection->getInstance('chupa')->delete();
        $collection->getInstance(TaskForAdapter::class)->delete();

        $task1 = new class{

            public function __invoke()
            {
                throw new TaskAssemblerException('Error');
            }
        };

        $dateTime = date('Y-m-d H:i:s', time() - 10);


        $task = new TaskAssembler($collection);
        $task->add($task1, 'chupa');
        $task->execute();

        $instance = $collection->getInstance('chupa');


        $this->assertEquals('Error', $instance->getMeta('exception_message'));
        $this->assertGreaterThan($dateTime, $instance->getMeta('exception_datetime'));

    }
}