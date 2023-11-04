<?php

namespace PHPFuse\Cache;

use PHPFuse\Cache\Interfaces\CacheException as CacheExceptionInterface;

class CacheException extends \InvalidArgumentException implements CacheExceptionInterface
{
}
