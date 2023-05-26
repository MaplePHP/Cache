<?php
// Place Codes/snippets at top of test file

require_once("autoloader.php");


use PHPFuse\Cache\Cache;
use PHPFuse\Cache\Handlers\FileSystemHandler;

use PHPFuse\Http\Stream;

// You can pass streams if you want
//$stream = new Stream(Stream::TEMP);
//$stream->write("Lorem ipsum dolor");

// PSR-16

$cache = new Cache(new FileSystemHandler(dirname(__FILE__)));

//var_dump($cache->delete("test"));

try {
	$expireInOneHour = time()+3600;
	if(!$cache->has("test") && $cache->set("test", ["Lorem 1", "Lorem 2"], $expireInOneHour)) {
		echo "Cache has been set...<br>";
	}
	echo "Get cache: ";
	print_r($cache->get("test"));

} catch (Exception $e) {
	// Log errors
	echo $e->getMessage();
}
die();

// PSR-6

$cache = new FileSystemHandler(dirname(__FILE__)."/");

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
