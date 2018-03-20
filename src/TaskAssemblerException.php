<?php
/**
 * ----------------------------------------------
 * | Author: Andrey Ryzhov (Dune) <info@rznw.ru> |
 * | Site: www.rznw.ru                           |
 * | Phone: +7 (4912) 51-10-23                   |
 * | Date: 20.03.2018                            |
 * -----------------------------------------------
 *
 */


namespace AndyDune\TaskLock;


class TaskAssemblerException extends \Exception
{
    protected $nextExecutionDelay = null;

    /**
     * @return null|int
     */
    public function getNextExecutionDelay()
    {
        return $this->nextExecutionDelay;
    }

    /**
     * @param null $nextExecutionDelay
     */
    public function setNextExecutionDelay($nextExecutionDelay): void
    {
        $this->nextExecutionDelay = (int)$nextExecutionDelay;
    }

}