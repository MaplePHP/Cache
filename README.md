# PHPFuse - Cache
PHPFuse Cache, a clean and user-friendly caching library. PHPFuse Cache is designed to provide a seamless caching experience with simplicity and ease of use as its core principles. Whether you're familiar with **PSR-6** or **PSR-16**, this library offers a smooth and intuitive caching solution for your PHP applications.

## Initiate the cache engine
Start with initiate  the PHPFuse Cache class and pass on a Handler to it. 
```php
use PHPFuse\Cache\Cache;
use PHPFuse\Cache\Handlers\FileSystemHandler;

$cache = new Cache(new FileSystemHandler(dirname(__FILE__)."/storage/cache"));
```

## Usage
It is super easy to use
```php
$expireInOneHour = time()+3600;
if(!$cache->has("test") && $cache->set("test", "Lorem ipsum dolor", $expireInOneHour)) {
	echo "Cache has been set<br>";
}
echo "Get cache: ".$cache->get("test");
```
### Error handling
While not required, it is advisable to handle critical throwable errors that may occur, such as an invalid cache key, insufficient file permissions, or possible connection issues with a cache server. Logging these exceptions can be beneficial for error tracking and troubleshooting purposes. 
```php
$cache = new Cache(new FileSystemHandler(dirname(__FILE__)));
try {
	// Invalid key set
	print_r($cache->get("te st"));
} catch (Exception $e) {
	// Will trigger: Invalid cache key. Only alphanumeric characters, underscores, and dots are allowed.
	echo $e->getMessage();
}
```

## Command list (PSR-16)
#### Get a cache item
Can return mixed values if successful if miss then return default value. The default value is **not** required and is by default **null**.
```php
$cache->get("test", "Default value");
```
#### Check if cache item exists
Will return bool
```php
$cache->has("test");
```
#### Set a cache item
It is allowed to set cache values of mixed types like strings, arrays and including PSR-7: HTTP Streams. Will return bool.
```php
// Set cache with 1 hour lifetime
$cache->set("test", "Lorem ipsum dolor", time()+3600);
// Set cache that will persist
$cache->set("test2", "Lorem ipsum dolor");
```
#### Delete a cache item
Will return bool
```php
$cache->delete("test");
```
#### Clear and auto delete all cache items
Will return bool
```php
$cache->clear();
```
#### Get multiple cache items
Will return array with mixed values if successful, if miss then return default value. The default value is **not** required and is by default **null**.
```php
$cache->getMultiple(["test1", "test2"], "Default value");
```
#### Set multiple cache items
Will return bool 
```php
$cache->setMultiple(["test1" => "Lorem", "test2" => "Ipsum"], time()+3600);
```
#### Delete multiple cache items
Will return bool
```php
$cache->deleteMultiple(["test1", "test2"]);
```
## PSR-6 Example
If your application needs advanced caching features, hierarchical caching, or cache tagging, PSR-6 is a more suitable choice.
```php

use PHPFuse\Cache\Handlers\FileSystemHandler;
$cache = new FileSystemHandler(dirname(__FILE__)."/storage/cache");

$item = $cache->getItem('test');

try {
	if(!$item->isHit()) {
		$item->set(["Lorem 1", "Lorem 2"])->expiresAfter(time()+3600);
		$cache->save($item);

		echo "Insert to cache: ";
		print_r($item->get());

	} else {
		echo "Read from cache: ";
		print_r($item->get());
	}

} catch (Exception $e) {
	echo $e->getMessage();
}
```
