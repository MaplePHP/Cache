<?php

namespace PHPFuse\Cache\Handlers;

use PHPFuse\Cache\Interfaces\CacheItemInterface;
use PHPFuse\Cache\Exceptions\CacheException;
use PHPFuse\Cache\CachePoolAbstract;
use Memcached;

class MemcachedHandler extends CachePoolAbstract
{
    public const HOST = "127.0.0.1";
    public const PORT = 11211;
    public const WEIGHT = 0;

    private $handler;
    private $servers = array();
    private $stats;

    public function __construct(string|array $host, ?int $port = null, int $weight = 0)
    {

        if (!class_exists("Memcached")) {
            throw new CacheException("The PHP package \"Memcached\" is missing!", 1);
        }


        $this->handler = new Memcached();
        if (is_string($host)) {
            $this->servers[] = [$host, $port, $weight];
        } else {
            $this->servers = $host;
        }
        $this->connect();
    }

    /**
     * Get all set keys
     * e.g. Some key may have already expired and wont be removed before @mem->get("KEY_NAME") has been called!
     * @return array
     */
    public function getAllKeys(): array
    {
        $arr = $this->handler->getAllKeys();
        if ($arr === false) {
            $this->validate();
            return [];
        }
        return $arr;
    }

    /**
     * This will Pass on cache content to CacheAbstract::getItem
     * @param  CacheItemInterface $item
     * @return void
     */
    protected function setItem(CacheItemInterface $item): void
    {
        $key = $item->getKey();
        if ($data = $this->handler->get($key)) {
            if (($data = unserialize($data)) && isset($data['expiresAfter'])) {
                $item->set($data['value']);
                $item->expiresAfter((int)$data['expiresAfter']);
            }
        }
    }

    /**
     * Clear and remove all cache items and data
     * @return bool
     */
    protected function setClear(): bool
    {
        if (($data = $this->getAllKeys()) && count($data) > 0) {
            $rsp = $this->handler->deleteMulti($data);
            $this->validate();
            foreach ($rsp as $v) {
                if ((int)$v !== 1) {
                    return false;
                }
            }
            return true;
        }
        return false;
    }

    /**
     * Clear and remove cache item and data
     * @param  string  $key
     * @return bool
     */
    protected function setDelete($key): bool
    {
        $bool = $this->handler->delete($key);
        $this->validate();
        return $bool;
    }

    /**
     * Create cache
     * @param  CacheItemInterface  $item
     * @return bool
     */
    protected function setSave(CacheItemInterface $item): bool
    {
        $data = serialize([
            "value" => $item->get(),
            "expiresAfter" => $this->setExpiration($item)
        ]);
        $bool = $this->handler->set($item->getKey(), $data, $item->getExpiration());
        $this->validate();
        return $bool;
    }

    /**
     * Get Memcached handler
     * @return Memcached
     */
    final public function getMemcached(): Memcached
    {
        return $this->handler;
    }

    /**
     * Validate response
     * @return bool
     */
    final protected function validate(): bool
    {
        if (
            $this->handler->getResultCode() !== Memcached::RES_SUCCESS &&
            $this->handler->getResultCode() !== Memcached::RES_NOTFOUND
        ) {
            throw new CacheException($this->handler->getResultMessage(), 1);
            //return false;
        }
        return true;
    }

    /**
     * Connect to server-/s
     * @return void
     */
    final protected function connect(): void
    {
        if (is_null($this->stats)) {
            $this->validateServers();
            $this->handler->addServers($this->servers);
            $this->stats = $this->handler->getStats();
            if ($this->stats === false) {
                throw new CacheException("One or more server connection has failed!", 1);
            }
        }
    }

    /**
     * Validate server information
     * @return void
     */
    final protected function validateServers(): void
    {
        if (count($this->servers) > 0) {
            foreach ($this->servers as $server) {
                $host = ($server[0] ?? null);
                $port = ($server[1] ?? null);
                $weight = ($server[1] ?? 0);
                if (!is_string($host)) {
                    throw new CacheException("Expecting a string value in argumnet 1 (IP/Host) but " .
                        "got instead a " . gettype($host) . ".", 1);
                }
                if (!is_int($port)) {
                    throw new CacheException("Expecting a int value argumnet 2 (port) but " .
                        "got instead a " . gettype($port) . ".", 1);
                }
                if (!is_int($weight)) {
                    throw new CacheException("Expecting a int value argumnet 3 (weight) but " .
                        "got instead a " . gettype($weight) . ".", 1);
                }
            }
        } else {
            throw new CacheException("Could not find any servers", 1);
        }
    }
}
