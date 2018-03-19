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
        $this->functions[] = [
            'function' => $instance,
            'name' => $name ?? get_class($instance),
            'interval' => $interval ?? $this->interval,
        ];
        return $this;
    }

    public function execute($breakOnExecute = false)
    {
        $executed = 0;
        foreach ($this->functions as $row) {
            $instance = $this->collection->getInstance($row['name']);
            if (!$instance->isReady()) {
                continue;
            }
            $instance->lock();
            $function = $row['function'];
            if (is_string($function)) {
                $function = new $function;
                $this->initialize($instance);
            }
            $this->initialize($instance);

            $executed++;
            call_user_func($function);

            $instance->unlock($row['interval']);
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

}