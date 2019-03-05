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
use AndyDune\ConditionalExecution\ConditionHolder;

class Instance
{
    protected $name;

    /**
     * @var AdapterInterface
     */
    protected $adapter;

    protected $maxTimeToExecute = 1200;

    public function __construct($name, AdapterInterface $adapter)
    {
        $this->name = $name;
        $this->adapter = $adapter;
    }


    public function setMaxTimeFotTaskExecution($time)
    {
        $this->maxTimeToExecute = $time;
        return $this;
    }

    public function getMaxTimeFotTaskExecution()
    {
        return $this->maxTimeToExecute;
    }

    public function isReady()
    {
        $data = $this->adapter->get($this->name);

        $readyCheck  = new ConditionHolder();
        $readyCheck->executeIfTrue(function () {
                return true;
            })
            ->executeIfFalse(function () {
                return false;
            });

        $readyCheck->add($data['allow']);

        $readyCheckRest = new ConditionHolder();
        $readyCheckRest->bindOr();

        $readyCheck->add($readyCheckRest);


        $timeFormatNow = $this->formatDatetime();

        $readyCheckNotLocked  = new ConditionHolder();
        $readyCheckNotLocked->add(!$data['locked'])
            ->add($data['datetime_next'] <= $timeFormatNow);

        $readyCheckLocked  = new ConditionHolder();
        $readyCheckLocked->add($data['locked'])
            ->add($data['datetime_free'] <= $timeFormatNow);

        $readyCheckRest->add($readyCheckNotLocked)
            ->add($readyCheckLocked);

        return $readyCheck->doIt();
    }

    public function allow()
    {
        $this->adapter->set($this->name, ['allow' => true]);
        return $this;
    }

    public function disallow()
    {
        $this->adapter->set($this->name, ['allow' => false]);
        return $this;
    }

    public function getMeta($key)
    {
        $data = $this->adapter->get($this->name);
        return $data['meta'][$key] ?? null;
    }

    public function setMeta($key, $value)
    {
        $data = $this->adapter->get($this->name);
        $meta = $data['meta'] ?? [];
        $meta[$key] = $value;
        $this->adapter->set($this->name, ['meta' => $meta]);
        return $this;
    }


    /**
     * Change datetime for next execution.
     *
     * @param $seconds
     * @return $this
     */
    public function setDatetimeNext($seconds) : self
    {
        $data = [
            'datetime_next' => $this->formatDatetime(time() + $seconds),
        ];
        $this->adapter->set($this->name, $data);
        return $this;
    }

    public function delete()
    {
        $this->adapter->delete($this->name);
    }

    public function lock()
    {
        $data = [
            'locked' => true,
            'datetime_lock' => $this->formatDatetime(),
            'datetime_free' => $this->formatDatetime(time() + $this->maxTimeToExecute),
        ];
        $this->adapter->set($this->name, $data);
    }

    public function unlock($pause = 0)
    {
        $data = [
            'locked' => false,
            'datetime_free' => $this->formatDatetime(),
            'datetime_next' => $this->formatDatetime(time() + $pause),
        ];
        $this->adapter->set($this->name, $data);
    }

    protected function formatDatetime($time = null)
    {
        if (!$time) {
            $time = time();
        }
        return $time;
    }
}