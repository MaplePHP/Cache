<?php

namespace MaplePHP\Cache\Handlers;

use MaplePHP\Cache\Interfaces\CacheItemInterface;
use MaplePHP\Cache\Exceptions\CacheException;
use MaplePHP\Cache\CachePoolAbstract;

class FileSystemHandler extends CachePoolAbstract
{
    private $cacheDirectory;

    public function __construct(string $cacheDirectory)
    {
        $this->cacheDirectory = rtrim($cacheDirectory, "/");
    }

    /**
     * This will Pass on cache content to CacheAbstract::getItem
     * @param  CacheItemInterface $item
     * @return void
     */
    protected function setItem(CacheItemInterface $item): void
    {
        $key = $item->getKey();
        $path = $this->getCacheFilePath($key);

        if (is_file($path)) {
            if (!is_readable($path)) {
                throw new CacheException("The cache file ({$path}) is not readable!", 1);
            }
            $data = file_get_contents($path);

            if (($data = unserialize($data)) && isset($data['expiresAfter'])) {
                $item->set($data['value']);
                $item->expiresAfter((int)$data['expiresAfter']);
            }
        }
    }

    /**
     * Get all set keys
     * @return array
     */
    public function getAllKeys(): array
    {
        $new = array();
        $files = glob("{$this->cacheDirectory}/*.cache");
        foreach ($files as $file) {
            $file = basename($file);
            $exp = explode(".", $file);
            array_pop($exp);
            $new[] = implode(".", $exp);
        }
        return $new;
    }

    /**
     * Clear and remove all cache items and data
     * @return bool
     */
    protected function setClear(): bool
    {
        $files = glob("{$this->cacheDirectory}/*.cache");
        foreach ($files as $file) {
            if (is_file($file) && is_writable($file)) {
                unlink($file);
            } else {
                return false;
            }
        }
        return true;
    }

    /**
     * Clear and remove cache item and data
     * @param  string  $key
     * @return bool
     */
    protected function setDelete($key): bool
    {
        $path = $this->getCacheFilePath($key);
        if (is_file($path) && is_writable($path)) {
            unlink($path);
            return true;
        }
        return false;
    }

    /**
     * Create cache
     * @param  CacheItemInterface  $item
     * @return bool
     */
    protected function setSave(CacheItemInterface $item): bool
    {
        if (!is_dir($this->cacheDirectory)) {
            throw new CacheException("The cache directory is not a directory: {$this->cacheDirectory}", 1);
        }
        if (!is_writeable($this->cacheDirectory)) {
            throw new CacheException("The cache filesystem directory is not writable!", 1);
        }

        $data = serialize([
            "value" => $item->get(),
            "expiresAfter" => $this->setExpiration($item)
        ]);

        $path = $this->getCacheFilePath($item->getKey());
        $size = file_put_contents($path, $data);
        return ($size !== false);
    }

    /**
     * Get cache file path
     * @param  string $key
     * @return string
     */
    protected function getCacheFilePath(string $key): string
    {
        return "{$this->cacheDirectory}/{$key}.cache";
    }
}
