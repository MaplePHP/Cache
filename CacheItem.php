<?php

namespace PHPFuse\Cache;

use PHPFuse\Cache\Interfaces\CacheItemInterface;
use DateTimeInterface, DateInterval, DateTime;

class CacheItem implements CacheItemInterface {

	private $key;
    private $value;
    private $isHit;
    private $expiresAt;

    /**
     * Store checke item to this middle hand class object for Cache pool
     * @param string $key [description]
     */
    public function __construct(string $key)
    {
        $this->key = $key;
        $this->isHit = false;
    }

    /**
     * Get cache item key
     * @return string
     */
    public function getKey(): string
    {
        return $this->key;
    }

    /**
     * Get cache item
     * @return mixed
     */
    public function get(): mixed
    {
        return ($this->isHit()) ? $this->value : NULL;
    }

    /**
     * Confirms if the cache item lookup resulted in a cache hit.
     * @return bool
     */
    public function isHit(): bool
    {
        return $this->isHit;
    }

    /**
     * Set cache item
     * @param mixed $value
     */
    public function set(mixed $value): static
    {
        $this->value = $value;
        $this->isHit = true;
        return $this;
    }

    /**
     * Set expiration date with DateTimeInterface
     * @param  DateTimeInterface $expiration
     * @return static
     */
    public function expiresAt(?DateTimeInterface $expiration): static
    {
        $this->expiresAt = $expiration;
        return $this;
    }

    /**
     * Set expiration date with Int or DateInterval
     * @param  int|DateInterval|null   $expiration
     * @return static
     */
    public function expiresAfter(int|DateInterval|null $expiration): static 
    {
    	$this->expiresAt = $expiration;
    	return $this;
    }

    /**
     * Return expiration 
     * @return int
     */
    public function getExpiration(): mixed
    {
    	if($this->expiresAt instanceof DateTimeInterface) {
            return $this->expiresAt->getTimestamp();

        } elseif (is_int($this->expiresAt)) {
        	return $this->expiresAt;
            
        } else {
            throw new \InvalidArgumentException('Invalid expiration provided');
        }

        return $this->expiresAt;
    }
}
