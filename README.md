# FyreCache

**FyreCache** is a free, open-source cache library for *PHP*.


## Table Of Contents
- [Installation](#installation)
- [Methods](#methods)
- [Cachers](#cachers)
    - [File](#file)
    - [Memcached](#memcached)
    - [Redis](#redis)



## Installation

**Using Composer**

```
composer require fyre/cache
```

In PHP:

```php
use Fyre\Cache\Cache;
```


## Methods

**Clear**

Clear all instances and configs.

```php
Cache::clear();
```

**Get Config**

Get a [*Cacher*](#cachers) config.

- `$key` is a string representing the [*Cacher*](#cachers) key.

```php
$config = Cache::getConfig($key);
```

**Get Key**

Get the key for a [*Cacher*](#cachers) instance.

- `$cacher` is a [*Cacher*](#cachers).

```php
$key = Cache::getKey($cacher);
```

**Has Config**

Check if a [*Cacher*](#cachers) config exists.

- `$key` is a string representing the [*Cacher*](#cachers) key, and will default to `Cache::DEFAULT`.

```php
$hasConfig = Cache::hasConfig($key);
```

**Init Config**

Initialize a set of config options.

- `$config` is an array containing key/value pairs of config options.

```php
Cache::initConfig($config);
```

**Is Loaded**

Check if a [*Cacher*](#cachers) instance is loaded.

- `$key` is a string representing the [*Cacher*](#cachers) key, and will default to `Cache::DEFAULT`.

```php
$isLoaded = Cache::isLoaded($key);
```

**Load**

Load a [*Cacher*](#cachers).

- `$options` is an array containing configuration options.

```php
$cacher = Cache::load($options);
```

**Set Config**

Set the [*Cacher*](#cachers) config.

- `$key` is a string representing the [*Cacher*](#cachers) key.
- `$options` is an array containing configuration options.

```php
Cache::setConfig($key, $options);
```

**Unload**

Unload a [*Cacher*](#cachers).

- `$key` is a string representing the [*Cacher*](#cachers) key, and will default to *"default"*.

```php
Cache::unload($key);
```

**Use**

Load a shared [*Cacher*](#cachers) instance.

- `$key` is a string representing the [*Cacher*](#cachers) key, and will default to *"default"*.

```php
$cacher = Cache::use($key);
```


## Cachers

You can load a specific cacher by specifying the `className` option of the `$options` variable above.

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


### File

The File cacher can be loaded using custom configuration.

- `$key` is a string representing the cacher key.
- `$options` is an array containing configuration options.
    - `className` must be set to `\Fyre\Cache\Handlers\FileCacher`.
    - `expire` is a number indicating the default cache timeout.
    - `prefix` is a string representing the cache key prefix.
    - `path` is a string representing the directory path, and will default to "*/tmp/cache*".
    - `mode` is a number indicating the cache file permissions, and will default to *0640*.

```php
Cache::setConfig($key, $options);

$cacher = Cache::use($key);
```


### Memcached

The Memcached cacher can be loaded using custom configuration.

- `$key` is a string representing the cacher key.
- `$options` is an array containing configuration options.
    - `className` must be set to `\Fyre\Cache\Handlers\MemcachedCacher`.
    - `expire` is a number indicating the default cache timeout.
    - `prefix` is a string representing the cache key prefix.
    - `host` is a string representing the Memcached host, and will default to "*127.0.0.1*".
    - `port` is a number indicating the Memcached port, and will default to *11211*.
    - `weight` is a number indicating the server weight, and will default to *1*.

```php
Cache::setConfig($key, $options);

$cacher = Cache::use($key);
```


### Redis

The Redis cacher can be loaded using custom configuration.

- `$key` is a string representing the cacher key.
- `$options` is an array containing configuration options.
    - `className` must be set to `\Fyre\Cache\Handlers\RedisCacher`.
    - `expire` is a number indicating the default cache timeout.
    - `prefix` is a string representing the cache key prefix.
    - `host` is a string representing the Redis host, and will default to "*127.0.0.1*".
    - `password` is a string representing the Redis password.
    - `port` is a number indicating the Redis port, and will default to *6379*.
    - `database` is a string representing the Redis database.
    - `timeout` is a number indicating the connection timeout.

```php
Cache::setConfig($key, $options);

$cacher = Cache::use($key);
```