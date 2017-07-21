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
       ->setTable('product')
       ->setPrimaryKey('product_id')
       ->setFieldsToSearch(['product_name', 'product_description', 'product_seokeywords'])
       ->setConditions(['product_live' => 1]);

// Perform a search for 'test product', limited to top 10 results
$results = $search->query('test product', 10);

// Output results
var_dump($results);
```

The results are returned as an associative array, in the following format.

```php
Array
(
    // [Record Primary Key] => Relevance (higher is more relevant)

    [16647] => 402.17040273556
    [16651] => 402.04274717241
    [17190] => 401.14345056617
    [15348] => 303.11566587702
    [15345] => 303.04049844237
    [15349] => 302.90270308353
    [15347] => 302.81635802469
    [15344] => 302.78347032992
    [15346] => 302.61082737487
    [14532] => 302.27662411888
)
```