<?php

namespace MaplePHP\Cache\Exceptions;

use MaplePHP\Cache\Interfaces\CacheException as CacheExceptionInterface;

class CacheException extends \InvalidArgumentException implements CacheExceptionInterface
{
}
