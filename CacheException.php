<?php

namespace MaplePHP\Cache;

use MaplePHP\Cache\Interfaces\CacheException as CacheExceptionInterface;

class CacheException extends \InvalidArgumentException implements CacheExceptionInterface
{
}
