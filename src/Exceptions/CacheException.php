<?php

namespace MaplePHP\Cache\Exceptions;

use Psr\Cache\CacheException as PsrCacheException;

class CacheException extends \InvalidArgumentException implements PsrCacheException
{
}
