# Mango PHP Library

This is a PHP library that allows interaction with [Mango API](https://developers.getmango.com/en/api/?platform=php)

## Installation

### Dependencies

    * PHP 5.3+

### Install with Composer

If you're using [Composer](https://github.com/composer/composer), add this to
your composer.json `require`:

```javascript
{
  "require" : {
    "mango/mango-php" : "dev-master"
  }
}
```

And load it using Composer's autoloader

```php
require 'vendor/autoload.php';
```

### Install from GitHub

To install the source code:

```bash
$ git clone git@github.com:mango/mango-php.git
```

Include `mango-php` in your code and autoload `requests`:

```php
require_once '/path/to/mango-php/mango.php';
require_once '/path/to/rmccue/requests/Requests.php';
Requests::register_autoloader();
```

## Documentation
Documentation is available at https://developers.getmango.com/en/api/?platform=php

## Usage

### Set your secret API key:
```php
$mango = new Mango\Mango(array(
    "api_key" => "YOUR_SECRET_API_KEY"
));
```

### Create a customer
In order to create a Customer, you must call the `create()` method with [the required arguments](https://developers.getmango.com/en/api/charges/?platform=php#arguments).

```php
$customer = $mango->Customers->create(array(
    "email" => "test-php@example.org",
    "name" => "Test Customer"
));
var_dump($customer);
```

### Get single customer
When you have a customer `uid`, you can get a full detail using the `get()` method:

```php
$customer = $mango->Customers->get("customer_1uqh884oy1ujh9y9eatm0jo3zxu0rm2s");
var_dump($customer);
```

You can also work with all the other resources authenticated with a secret API Key:
- [Charges](https://developers.getmango.com/en/api/charges/?platform=php)
- [Refunds](https://developers.getmango.com/en/api/refunds/?platform=php)
- [Customers](https://developers.getmango.com/en/api/customers/?platform=php)
- [Cards](https://developers.getmango.com/en/api/cards/?platform=php)
- [Queue](https://developers.getmango.com/en/api/queue/?platform=php)
- [Installments](https://developers.getmango.com/en/api/installments/?platform=php)
- [Promotions](https://developers.getmango.com/en/api/promotions/?platform=php)

## Tests

Install the module along with the dev dependencies using composer:
```bash
$ git clone git://github.com/mango/mango-php.git
$ cd mango-php
$ composer install
```

To run the tests you'll need Mango API keys (mode Sandbox):
```bash
export MANGO_SECRET_TEST_KEY='your secret test API key'
export MANGO_PUBLIC_TEST_KEY='your public test API Key'
```

### Run the tests
```bash
$ phpunit test
```

### Run code coverage
To run the code coverage you'll need Xdebug

```bash
$ phpunit --coverage-html coverage
```

## License
Licensed under the MIT license.

Copyright (c) 2014 Mango.
