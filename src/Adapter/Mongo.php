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


namespace AndyDune\TaskLock\Adapter;
use MongoDB\Collection;

class Mongo implements AdapterInterface
{
    protected $id = null;

    /**
     * @var \MongoDB\Collection
     */
    protected $collection;

    /**
     * @return Collection
     */
    public function getCollection()
    {
        return $this->collection;
    }


    public function setCollection(Collection $collection)
    {
        $this->collection = $collection;
        return $this;
    }

    public function get($name)
    {
        $data =  $this->getCollection()->findOne(['name' => $name]);
        $dataToReturn = [
            'name' => $name,
            'locked' => $data['locked'] ?? false,
            'allow' => $data['allow'] ?? true,
            'datetime_lock' => $data['datetime_lock'] ?? 0,
            'datetime_free' => $data['datetime_free'] ?? 0,
            'datetime_next' => $data['datetime_next'] ?? 0,
            'step' => $data['step'] ?? 0,
            'meta' => $data['meta'] ?? [],
        ];
        return $dataToReturn;
    }

    public function set($name, $data)
    {
        $this->getCollection()->findOneAndUpdate(['name' => $name], ['$set' => $data], ['upsert' => true]);
        return $this;
    }

    public function delete($name)
    {
        $this->getCollection()->deleteMany(['name' => $name]);
        return $this;
    }

}