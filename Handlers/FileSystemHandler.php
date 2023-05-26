<?php
namespace PHPFuse\Cache\Handlers;

use PHPFuse\Cache\Interfaces\CacheItemInterface;
use PHPFuse\Cache\Exceptions\CacheException;
use PHPFuse\Cache\CachePoolAbstract;


class FileSystemHandler extends CachePoolAbstract
{

    private $cacheDirectory;

    function __construct(string $cacheDirectory) 
    {
        $this->cacheDirectory = rtrim($cacheDirectory, "/");
    }

    /**
     * This will Pass on cache content to CacheAbstract::getItem
     * @param  CacheItemInterface $item
     * @return CacheItemInterface
     */
    protected function setItem(CacheItemInterface $item): void
    {
        $key = $item->getKey();
        $path = $this->getCacheFilePath($key);

        if(is_file($path)) {
            if(!is_readable($path)) throw new CacheException("The cache file ({$path}) is not readable!", 1);
            $data = file_get_contents($path);

            if(($data = unserialize($data)) && isset($data['expiresAfter'])) {
                $item->set($data['value']);
                $item->expiresAfter((int)$data['expiresAfter']);
            }
            
        }
    }

    /**
     * Clear and remove all cache items and data
     * @param  string  $key
     * @return bool
     */
    protected function setClear(): bool
    {
        $files = glob("{$this->cacheDirectory}/*.cache");
        foreach($files as $file) {
            if(is_file($file) && is_writable($file)) {
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
        if(is_file($path) && is_writable($path)) {
            unlink($path);
            return true;
        }
        return false;
    }

    /**
     * Create cache
     * @param  string  $key
     * @return bool
     */
    protected function setSave(CacheItemInterface $item): bool 
    {
        if(!is_writeable($this->cacheDirectory)) throw new CacheException("The cache filesystem directory is not writable!", 1);


        $data = serialize([
            "value" => $item->get(),
            "expiresAfter" => $item->getExpiration()
        ]);

        $path = $this->getCacheFilePath($item->getKey());
        $size = file_put_contents($path, $data);
        return (bool)($size !== false);
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
