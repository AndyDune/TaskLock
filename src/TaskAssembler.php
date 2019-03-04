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


class TaskAssembler
{
    protected $name = null;
    protected $interval = 0;

    protected $functions = [];

    /**
     * @var Collection
     */
    protected $collection;

    protected $initializers = [];
    protected $initializersApplyed = [];

    protected $results = [];

    public function __construct(Collection $collection)
    {
        $this->collection = $collection;
    }

    public function setInterval($int)
    {
        $this->interval = $int;
        return $this;
    }

    public function add($instance, $name = null, $interval = null)
    {
        if (!$name) {
            $name = is_string($instance) ? $instance : get_class($instance);
        }
        $this->functions[$name] = [
            'function' => $instance,
            'name' => $name,
            'interval' => $interval ?? $this->interval,
        ];
        return $this;
    }

    public function execute($breakOnExecute = false)
    {
        $executed = 0;
        foreach ($this->functions as $key => $row) {
            $instance = $this->collection->getInstance($row['name']);
            if (!$instance->isReady()) {
                continue;
            }
            $instance->lock();
            $function = $row['function'];
            if (is_string($function)) {
                $this->functions[$key]['function'] = $function = new $function;
            }
            // Fir execution repeat
            if (!in_array($row['name'], $this->initializersApplyed)) {
                $this->initializersApplyed[] = $row['name'];
                $this->initialize($function);
            }

            $executed++;

            try {
                $this->results[$row['name']] = call_user_func($function);
                $interval = $row['interval'];
            } catch (TaskAssemblerException $e) {
                $interval = $e->getNextExecutionDelay();
                $instance->setMeta('exception_message', $e->getMessage());
                $instance->setMeta('exception_datetime', date('Y-m-d H:i:s'));
            }

            $instance->unlock($interval);
            if ($breakOnExecute) {
                break;
            }
        }
        return $executed;
    }

    /**
     * Add initializer for DI with interface.
     *
     * @param callable $initializer
     * @return $this
     */
    public function addInitializer(callable $initializer)
    {
        $this->initializersApplyed = [];
        $this->initializers[] = $initializer;
        return $this;
    }

    protected function initialize($instance)
    {
        if (!$this->initializers) {
            return;
        }
        foreach ($this->initializers as $initializer) {
            $initializer($instance);
        }
        return;
    }

    public function getResults()
    {
        return $this->results;
    }

}