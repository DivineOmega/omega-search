<?php
use \RapidWeb\Search\Search;

$pdo = new PDO('mysql:dbname=database_name;host=127.0.0.1', 'username', 'password');

$search = new Search;

$search->setDatabaseConnection($pdo)
       ->setTable('products')
       ->setPrimaryKey('product_id')
       ->setFieldsToSearch(['product_name', 'product_description', 'product_seokeywords'])
       ->setConditions(['product_live' => 1]);
         
$results = $search->query('test product', 10);

var_dump($results);