# FyreCache

**FyreCache** is a free, open-source cache library for *PHP*.


## Table Of Contents
- [Installation](#installation)
- [Basic Usage](#basic-usage)
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
use Fyre\Cache\CacheManager;
```


## Basic Usage

- `$container` is a [*Container*](https://github.com/elusivecodes/FyreContainer).

```php
$cacheManager = new CacheManager($container);
```

Default configuration options will be resolved from the "*Cache*" key in the [*Config*](https://github.com/elusivecodes/FyreConfig) using the [*Container*](https://github.com/elusivecodes/FyreContainer).


## Methods

**Build**

Build a [*Cacher*](#cachers).

- `$options` is an array containing configuration options.

```php
$cacher = $cacheManager->build($options);
```

[*Cacher*](#cachers) dependencies will be resolved automatically from the [*Container*](https://github.com/elusivecodes/FyreContainer).

**Clear**

Clear all instances and configs.

```php
$cacheManager->clear();
```

**Disable**

Disable the cache.

```php
$cacheManager->disable();
```
If the cache is disabled, the `use` method will always return a *NullCacher*.

**Enable**

Enable the cache.

```php
$cacheManager->enable();
```

**Get Config**

Get a [*Cacher*](#cachers) config.

- `$key` is a string representing the [*Cacher*](#cachers) key.

```php
$config = $cacheManager->getConfig($key);
```

Alternatively, if the `$key` argument is omitted an array containing all configurations will be returned.

```php
$config = $cacheManager->getConfig();
```

**Has Config**

Determine whether a [*Cacher*](#cachers) config exists.

- `$key` is a string representing the [*Cacher*](#cachers) key, and will default to `CacheManager::DEFAULT`.

```php
$hasConfig = $cacheManager->hasConfig($key);
```

**Is Enabled**

Determine whether the cache is enabled.

```php
$cacheManager->isEnabled();
```

**Is Loaded**

Determine whether a [*Cacher*](#cachers) instance is loaded.

- `$key` is a string representing the [*Cacher*](#cachers) key, and will default to `CacheManager::DEFAULT`.

```php
$isLoaded = $cacheManager->isLoaded($key);
```

**Set Config**

Set the [*Cacher*](#cachers) config.

- `$key` is a string representing the [*Cacher*](#cachers) key.
- `$options` is an array containing configuration options.

```php
$cacheManager->setConfig($key, $options);
```

**Unload**

Unload a [*Cacher*](#cachers).

- `$key` is a string representing the [*Cacher*](#cachers) key, and will default to `CacheManager::DEFAULT`.

```php
$cacheManager->unload($key);
```

**Use**

Load a shared [*Cacher*](#cachers) instance.

- `$key` is a string representing the [*Cacher*](#cachers) key, and will default to `CacheManager::DEFAULT`.

```php
$cacher = $cacheManager->use($key);
```

[*Cacher*](#cachers) dependencies will be resolved automatically from the [*Container*](https://github.com/elusivecodes/FyreContainer).


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

Determine whether an item exists in the cache.

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

- `$options` is an array containing configuration options.
    - `className` must be set to `\Fyre\Cache\Handlers\FileCacher`.
    - `expire` is a number indicating the default cache timeout.
    - `prefix` is a string representing the cache key prefix.
    - `path` is a string representing the directory path, and will default to "*/tmp/cache*".
    - `mode` is a number indicating the cache file permissions, and will default to *0640*.

```php
$cacher = $cacheManager->build($options);
```


### Memcached

The Memcached cacher can be loaded using custom configuration.

- `$options` is an array containing configuration options.
    - `className` must be set to `\Fyre\Cache\Handlers\MemcachedCacher`.
    - `expire` is a number indicating the default cache timeout.
    - `prefix` is a string representing the cache key prefix.
    - `host` is a string representing the Memcached host, and will default to "*127.0.0.1*".
    - `port` is a number indicating the Memcached port, and will default to *11211*.
    - `weight` is a number indicating the server weight, and will default to *1*.

```php
$cacher = $cacheManager->build($options);
```


### Redis

The Redis cacher can be loaded using custom configuration.

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
$cacher = $cacheManager->build($options);
```