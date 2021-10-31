# FyreCache

**FyreCache** is a free, cache library for *PHP*.


## Table Of Contents
- [Installation](#installation)
- [Methods](#methods)
- [Cachers](#cachers)



## Installation

**Using Composer**

```
composer install fyre/cache
```

In PHP:

```php
use Fyre\Cache\Cache;
```


## Methods

**Clear**

Clear instances.

```php
Cache::clear();
```

**Load**

Load an cacher.

- `$config` is an array containing the configuration for the cacher.

```php
$cacher = Cache::load($config);
```

**Set Config**

Set the cacher config.

- `$key` is a string representing the cacher key.
- `$config` is an array containing configuration data.

```php
Cache::setConfig($key, $config);
```

**Use**

Load a shared cacher instance.

- `$key` is a string representing the cacher key, and will default to *"default"*.

```php
$cacher = Cache::use($key);
```


## Cachers

You can load a specific encrypter by specifying the `className` option of the `$config` variable above.

The default cachers are:
- `\Fyre\Cache\Handlers\FileCacher`
- `\Fyre\Cache\Handlers\MemcachedCacher`
- `\Fyre\Cache\Handlers\RedisCacher`

Custom cachers can be created by extending `\Fyre\Cache\Cacher`, ensuring all below methods are implemented.

**Decrement**

Decrement a cache value.

- `$key` is a string representing the cache key.
- `$amount` is a number indicating the amount to decrement by, and will default to *1*.

```php
$value = $cacher->decrement($key, $amount);
```

**Delete**

Delete an item from the cache.

- `$key` is a string representing the cache key.

```php
$deleted = $cacher->delete($key);
```

**Empty**

Empty the cache.

```php
$emptied = $cacher->empty();
```

**Get**

Retrieve a value from the cache.

- `$key` is a string representing the cache key.

```php
$value = $cacher->get($key);
```

**Has**

Determine if an item exists in the cache.

- `$key` is a string representing the cache key.

```php
$has = $cacher->has($key);
```

**Increment**

Increment a cache value.

- `$key` is a string representing the cache key.
- `$amount` is a number indicating the amount to increment by, and will default to *1*.

```php
$value = $cacher->increment($key, $amount);
```

**Remember**

Retrieve an item from the cache, or save a new value if it doesn't exist.

- `$key` is a string representing the cache key.
- `$callback` is the callback method to generate the value.
- `$expire` is a number indicating the number of seconds the value will be valid, and will default to *null*.

```php
$value = $cacher->remember($key, $callback, $expire);
```

**Save**

Save an item in the cache.

- `$key` is a string representing the cache key.
- `$value` is the value to save in the cache.
- `$expire` is a number indicating the number of seconds the value will be valid, and will default to *null*.

```php
$saved = $cacher->save($key, $value, $expire);
```

**Size**

Get the size of the cache.

```php
$size = $cacher->size();
```