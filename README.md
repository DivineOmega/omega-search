# Search

Search allows you to easily add an intelligent search engine to your website or web application. 
It can be configured to search any database table.

## Installation

You can install this package with Composer.

```
composer require rapidwebltd/search
```

## Usage

Using Search is easy. Take a look at the following example.

```php
use \RapidWeb\Search\Search;

// Setup your database connection. 
// If you already have a connection setup, you can skip this step.
$pdo = new PDO('mysql:dbname=database_name;host=127.0.0.1', 'username', 'password');

// Create a new Search object
$search = new Search;

// Configure the Search object
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

The results are returned as a `SearchResults` object containing an array of `SearchResult` objects.
This `SearchResults` object also contains various statistics such as the highest, lowest and average relevances,
and the time taken to perform the search.
