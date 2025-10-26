<?php

namespace MaplePHP\Cache;

use Psr\Cache\CacheException as PsrCacheException;

class CacheException extends \InvalidArgumentException implements PsrCacheException
{
}
