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


class TaskForAdapter
{
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
        $this->resultHolder->setResult('remedy');
    }
}