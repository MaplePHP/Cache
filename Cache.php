<?php
namespace PHPFuse\Cache;

use PHPFuse\Cache\Interfaces\CacheItemPoolInterface;
use PHPFuse\Cache\Interfaces\CacheInterface;
use PHPFuse\Cache\Exceptions\CacheException;


class Cache implements CacheInterface
{

    private $handler;

    /**
     * Cache container that takes a Cache handler and make it easy for you to cache results
     * @param CacheItemPoolInterface $handler
     */
    function __construct(CacheItemPoolInterface $handler) 
    {
        $this->handler = $handler;
    }

    /**
     * Get all set keys
     * @return array|bool
     */
    public function getAllKeys(): array 
    {
        return $this->handler->getAllKeys();
    }

    /**
     * Get cache value
     * @param  string     $key      The key of the item
     * @param  mixed|null $default  Return default value if miss
     * @return mixed
     */
    public function get(string $key, mixed $default = NULL): mixed 
    {
        $item = $this->handler->getItem($key);
        return ($item->isHit()) ? $item->get() : $default;
    }

    /**
     * Set cache value
     * @param string             $key   The key of the item to store.
     * @param mixed              $value The value of the item to store.
     * @param \DateInterval|null $ttl   TTL (seconds) cache lifetime from NOW 
     */
    public function set(string $key, mixed $value, NULL|int|\DateInterval $ttl = NULL): bool 
    {
        $item = $this->handler->getItem($key);
        $item->set($value)->expiresAfter((int)$ttl);
        return $this->handler->save($item);
    }

    /**
     * Delete cache
     * @param  string $key The key of the item to delete.
     * @return bool
     */
    public function delete(string $key): bool 
    {
        return $this->handler->deleteItem($key);
    }

    /**
     * Get multiple caches
     * @param  iterable   $keys    The keys of the items
     * @param  mixed|null $default Return default value if miss
     * @return iterable
     */
    public function getMultiple(iterable $keys, mixed $default = NULL): iterable 
    {
        $new = array();
        foreach($keys as $key) {
            $new[$key] = $this->get($key, $default);
        }
        return $new;
    }

    /**
     * Set cache value
     * @param array              $values   [KEY => VALUE] The key of the item to store and The value of the item to store.
     * @param \DateInterval|null $ttl       TTL (seconds) cache lifetime from NOW 
     */
    public function setMultiple(iterable $values, null|int|\DateInterval $ttl = NULL): bool 
    {
        foreach($values as $key => $val) {
            if(!$this->set($key, $val, $ttl)) return false;
        }
        return true;
    }

    /**
     * Delete multiple caches
     * @param  iterable $keys The keys of the items to delete
     * @return bool
     */
    public function deleteMultiple(iterable $keys): bool 
    {
        foreach($keys as $key) {
            if(!$this->delete($key)) return false;
        }
        return true;
    }

    /**
     * Item exists or has a hit
     * @param  string  $key The keys of the item
     * @return boolean
     */
    public function has(string $key): bool 
    {
        return $this->handler->hasItem($key);
    }

    /**
     * Clear and remove all cache items and data
     * @return bool
     */
    public function clear(): bool
    {
        return $this->handler->clear();
    }   
}
