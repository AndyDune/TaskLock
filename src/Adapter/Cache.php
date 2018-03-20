<?php
/**
 *
 * PHP version 7.X
 *
 * Adapter for psr-16 cache interface: https://www.php-fig.org/psr/psr-16/
 *
 * @package andydune/task-lock
 * @link  https://github.com/AndyDune/TaskLock for the canonical source repository
 * @license   http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Andrey Ryzhov  <info@rznw.ru>
 * @copyright 2018 Andrey Ryzhov
 */


namespace AndyDune\TaskLock\Adapter;
use Psr\SimpleCache\CacheInterface;

class Cache implements AdapterInterface
{
    protected $cacheObject;
    protected $ttl = null;

    public function setTtl($ttl = null)
    {
        $this->ttl = $ttl;
        return $this;
    }

    public function setCacheObject(CacheInterface $object)
    {
        $this->cacheObject = $object;
    }

    /**
     * @return CacheInterface
     */
    public function getCacheObject()
    {
        return $this->cacheObject;
    }

    public function get($name)
    {
        $data =  $this->getCacheObject()->get($name, []);
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
        $dataWas =  $this->getCacheObject()->get($name, []);
        $data = array_replace($dataWas, $data);
        $this->getCacheObject()->set($name, $data);
        return $this;
    }

    public function delete($name)
    {
        $this->getCacheObject()->delete($name);
        return $this;
    }
}