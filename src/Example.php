<?php
require __DIR__.'/../vendor/autoload.php';

use \DivineOmega\Search\Search;
use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;
use Cache\Adapter\Filesystem\FilesystemCachePool;

if (!class_exists('Cache\Adapter\Filesystem\FilesystemCachePool')) {
    die('This example requires the `FilesystemCachePool` class. Install it with `composer require cache/filesystem-adapter`.'.PHP_EOL);
}

$filesystemAdapter = new Local(__DIR__.'/');
$filesystem = new Filesystem($filesystemAdapter);
$cacheItemPool = new FilesystemCachePool($filesystem);

$pdo = new PDO('mysql:dbname=database_name;host=127.0.0.1', 'username', 'password');

$search = new Search;

$search->setDatabaseConnection($pdo)
       ->setCache($cacheItemPool, 60*60*24)
       ->setTable('products')
       ->setPrimaryKey('product_groupid')
       ->setFieldsToSearch(['product_name', 'product_description', 'product_seokeywords'])
       ->setConditions(['product_live' => 1]);
         
$results = $search->query('test product', 10);

var_dump($results);