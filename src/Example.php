<?php

require_once __DIR__.'/../vendor/autoload.php';

$pdo = new PDO('mysql:dbname=database_name;host=127.0.0.1', 'username', 'password');

$search = new \RapidWeb\Search\Search($pdo, 'products', 'product_id', ['product_name', 'product_description', 'product_seokeywords']);

$results = $search->query('pxielated', 10);

var_dump($results);
