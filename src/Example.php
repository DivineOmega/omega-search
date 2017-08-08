<?php
use \RapidWeb\Search\Search;

$pdo = new PDO('mysql:dbname=database_name;host=127.0.0.1', 'username', 'password');

$search = new Search;

$search->setDatabaseConnection($pdo)
//     ->setCache($cacheItemPool, 60*60*24)
       ->setTable('products')
       ->setPrimaryKey('product_groupid')
       ->setFieldsToSearch(['product_name', 'product_description', 'product_seokeywords'])
       ->setConditions(['product_live' => 1]);
         
$results = $search->query('test product', 10);

var_dump($results);