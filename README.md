# Omega Search

Omega Search allows you to easily add an intelligent search engine to your website or web application. 
It can be configured to search any database table.

If you are using the Laravel framework, take a look at the [Laravel Omega Search](https://github.com/DivineOmega/laravel-omega-search) package.

## Installation

You can install this package with Composer.

```
composer require divineomega/omega-search
```

## Usage

Using Omega Search is easy. Take a look at the following example.

```php
use \DivineOmega\OmegaSearch\OmegaSearch;

// Setup your database connection. 
// If you already have a connection setup, you can skip this step.
$pdo = new PDO('mysql:dbname=database_name;host=127.0.0.1', 'username', 'password');

// Create a new Omega Search object
$search = new OmegaSearch;

// Configure the Omega Search object
$search->setDatabaseConnection($pdo)
       ->setTable('products')
       ->setPrimaryKey('product_groupid')
       ->setFieldsToSearch(['product_name', 'product_description', 'product_seokeywords'])
       ->setConditions(['product_live' => 1]);

// Perform a search for 'test product', limited to top 10 results
$results = $search->query('test product', 10);

// Output results
var_dump($results);
```

The results are returned as a `SearchResults` object, as shown below, containing an array of `SearchResult` objects.
This `SearchResults` object also contains various statistics such as the highest, lowest and average relevances,
and the time taken to perform the search.

Each `SearchResult` object in the array provides the primary key `id` and its `relevance`. The `relevance` is 
simply a number that is higher on more relevant results. The array is sorted by relevance descending.

```php
object(DivineOmega\OmegaSearch\SearchResults)#731 (5) {
  ["results"]=>
  array(10) {
    [0]=>
    object(DivineOmega\OmegaSearch\SearchResult)#588 (2) {
      ["id"]=>
      int(80)
      ["relevance"]=>
      float(637.80198499153)
    }
    /** ... snipped ... */
    [9]=>
    object(DivineOmega\OmegaSearch\SearchResult)#597 (2) {
      ["id"]=>
      int(18469)
      ["relevance"]=>
      float(121.65783596237)
    }
  }
  ["highestRelevance"]=>
  float(637.80198499153)
  ["lowestRelevance"]=>
  float(121.65783596237)
  ["averageRelevance"]=>
  float(336.74613218217)
  ["time"]=>
  float(0.33661985397339)
}
```

### Overriding SQL

If you wish to write your own SQL query instead of using the one generated, for example if you wish to join another table, you can use the `setSqlOverride` method. The query passed into this method must contain a `SELECT` and `LIMIT ? , ?`, an exception will be thrown otherwise. You can omit the `setFieldsToSearch` method when overriding the SQL. 

Take a look at the following example:

```php
use \DivineOmega\OmegaSearch\OmegaSearch;

// Setup your database connection. 
// If you already have a connection setup, you can skip this step.
$pdo = new PDO('mysql:dbname=database_name;host=127.0.0.1', 'username', 'password');

// Create a new Omega Search object
$search = new OmegaSearch;

// Configure the Omega Search object
$search->setDatabaseConnection($pdo)
       ->setTable('products')
       ->setPrimaryKey('product_groupid')
       ->setConditions(['product_live' => 1])
       ->setSqlOverride('SELECT product_name, product_description, product_seokeywords FROM products LIMIT ? , ?');

// Perform a search for 'test product', limited to top 10 results
$results = $search->query('test product', 10);

// Output results
var_dump($results);
``` 

### Caching Source Data

To speed up searching, you can cache the source data using any PSR-6 compliant cache pool. An example of this is shown below.

```php
// Create cache pool
$filesystemAdapter = new Local(storage_path().'/search-cache/');
$filesystem = new Filesystem($filesystemAdapter);
$cacheItemPool = new FilesystemCachePool($filesystem);

// Set cache expiry time
$cacheExpiryInSeconds = 300;

// Create a new Omega Search object
$search = new OmegaSearch;

// Configure the Omega Search object
$search->setDatabaseConnection($pdo)
       ->setTable('products')
       ->setPrimaryKey('product_groupid')
       ->setFieldsToSearch(['product_name'])
       ->setCache($cacheItemPool, $cacheExpiryInSeconds); // Setup cache
```
