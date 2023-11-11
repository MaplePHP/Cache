<?php

namespace PHPFuse\Cache;

use PHPFuse\Cache\Interfaces\CacheItemInterface;
use PHPFuse\Cache\Interfaces\CacheItemPoolInterface;
use PHPFuse\Http\Interfaces\StreamInterface;
use PHPFuse\Cache\Exceptions\InvalidArgumentException;
use PHPFuse\Cache\CacheItem;

abstract class CachePoolAbstract implements CacheItemPoolInterface
{
    private $items = array();
    private $timestamp;

    /**
     * HANDLER: @setItems and the pass on to @getItems pool
     * @param CacheItemInterface $item
     * @return void
     */
    abstract protected function setItem(CacheItemInterface $item): void;

    /**
     * HANDLER: @setSave items and the pass on to @save pool
     * @param CacheItemInterface $item
     * @return bool
     */
    abstract protected function setSave(CacheItemInterface $item): bool;

    /**
     * HANDLER: @setDelete and the pass on to @delete pool
     * @param CacheItemInterface $item
     * @return bool
     */
    abstract protected function setDelete($key): bool;

    /**
     * HANDLER: @setClear and the pass on to @clear pool
     * @param CacheItemInterface $item
     * @return bool
     */
    abstract protected function setClear(): bool;


    /**
     * Get all keys
     * @return array|false
     */
    abstract public function getAllKeys(): array;



    /**
     * Get cache item instance
     * @param  string $key
     * @return CacheItemInterface
     */
    final public function getItem(string $key): CacheItemInterface
    {
        if (!isset($this->items[$key])) {
            $this->validateKey($key);

            $this->items[$key] = new CacheItem($key);
            $this->setItem($this->items[$key]);
            $value = $this->items[$key]->get();

            if (!is_null($value) && $this->hasItemExpired($this->items[$key])) {
                $this->items[$key] = new CacheItem($key);
            }
        }
        return $this->items[$key];
    }

    /**
     * Get multiple cache items
     * @param  array  $keys [description]
     * @return iterable
     */
    public function getItems(array $keys = []): iterable
    {
        $items = array();
        foreach ($keys as $key) {
            $items[$key] = $this->getItem($key);
        }
        return $items;
    }

    /**
     * Check if cache item exists
     * @param  string  $key
     * @return boolean
     */
    public function hasItem(string $key): bool
    {
        $item = $this->getItem($key);
        return (bool)($item->isHit() || !is_null($item->get()));
    }

    /**
     * Delete a cache item and data
     * @param  string  $key
     * @return bool
     */
    public function deleteItem($key): bool
    {
        $this->validateKey($key);
        if (isset($this->items[$key])) {
            unset($this->items[$key]);
        }
        return $this->setDelete($key);
    }

    /**
     * Delete multiple cache items and data
     * @param  array  $key
     * @return bool
     */
    public function deleteItems(array $keys): bool
    {
        foreach ($keys as $key) {
            if (!$this->deleteItem($key)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Clear and remove all cache items and data
     * @param  string  $key
     * @return bool
     */
    public function clear(): bool
    {
        $this->items = array();
        return $this->setClear();
    }

    /**
     * Save to cache file
     * @param  CacheItemInterface $item
     * @return bool
     */
    public function save(CacheItemInterface $item): bool
    {
        if ($this->saveDeferred($item)) {
            if (!$this->setSave($item)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Prepare save (wont be saved before commit is trggered)
     * @param  CacheItemInterface $item
     * @return bool
     */
    public function saveDeferred(CacheItemInterface $item): bool
    {
        $value = $item->get();
        if (!is_null($value)) {
            if ($value instanceof StreamInterface) {
                $value->seek(0);
                $value = $value->read($value->getSize());
            }
            return true;
        }
        return false;
    }

    /**
     * Commit prepared saves
     * @return bool
     */
    public function commit(): bool
    {
        foreach ($this->items as $key => $item) {
            if (!$this->save($item)) {
                return false;
            }
        }
        return true;
    }

    public function setExpiration(CacheItemInterface $item)
    {
        return ($item->getExpiration() > 0) ? time() + $item->getExpiration() : 0;
    }

    /**
     * Check if cache file has expired
     * @param  CacheItemInterface $item
     * @return bool
     */
    final public function hasItemExpired(CacheItemInterface $item): bool
    {
        $expiration = (int)$item->getExpiration();
        return (bool)($expiration > 0 && $expiration < $this->now());
    }

    /**
     * Get curret timestamp
     * @return int
     */
    final public function now(): int
    {
        if (is_null($this->timestamp)) {
            $d = new \DateTime("now");
            $this->timestamp = $d->getTimestamp();
        }
        return $this->timestamp;
    }

    /**
     * Validate the cache key
     * @param  string $key
     * @return void
     */
    final public function validateKey(string $key): void
    {
        if (!preg_match('/^[a-zA-Z0-9_\-.]+$/', $key)) {
            throw new InvalidArgumentException('Invalid cache key. Only alphanumeric characters, ' .
                'underscores, and dots are allowed.');
        }
    }
}
