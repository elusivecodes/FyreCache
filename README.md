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
- `$config` is a  [*Config*](https://github.com/elusivecodes/FyreConfig).

```php
$cacheManager = new CacheManager($container);
```

Default configuration options will be resolved from the "*Cache*" key in the [*Config*](https://github.com/elusivecodes/FyreConfig).

The cache will be disabled by default if the `"*App.debug*" key is set in the [*Config*](https://github.com/elusivecodes/FyreConfig).

**Autoloading**

It is recommended to bind the *CacheManager* to the [*Container*](https://github.com/elusivecodes/FyreContainer) as a singleton.

```php
$container->singleton(CacheManager::class);
```

Any dependencies will be injected automatically when loading from the [*Container*](https://github.com/elusivecodes/FyreContainer).

```php
$cacheManager = $container->use(CacheManager::class);
```


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

**Clear**

Clear the cache.

```php
$cleared = $cacher->clear();
```

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

**Delete Multiple**

Delete multiple items from the cache.

- `$keys` is an array containing the cache keys.

```php
$deleted = $cacher->deleteMultiple($keys);
```

**Get**

Retrieve a value from the cache.

- `$key` is a string representing the cache key.
- `$default` is the default value to return, and will default to *null*.

```php
$value = $cacher->get($key, $default);
```

**Get Multiple**

Retrieve multiple values from the cache.

- `$keys` is an array containing the cache keys.
- `$default` is the default value to return, and will default to *null*.

```php
$values = $cacher->getMultiple($keys, $default);
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

**Set**

Set an item in the cache.

- `$key` is a string representing the cache key.
- `$value` is the value to save in the cache.
- `$expire` is a number indicating the number of seconds the value will be valid, and will default to *null*.

```php
$saved = $cacher->set($key, $value, $expire);
```

**Set Multiple**

Set multiple items in the cache.

- `$values` is an array containing the values to save in the cache.
- `$expire` is a number indicating the number of seconds the value will be valid, and will default to *null*.

```php
$saved = $cacher->setMultiple($values, $expire);
```

**Size**

Get the size of the cache.

```php
$size = $cacher->size();
```


## Array

The Array cacher can be loaded using customer configuration.

- `$options` is an array containing configuration options.
    - `className` must be set to `\Fyre\Cache\Handlers\ArrayCacher`.
    - `expire` is a number indicating the default cache timeout.

```php
$container->use(Config::class)->set('Cache.array', $options);
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
$container->use(Config::class)->set('Cache.file', $options);
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
$container->use(Config::class)->set('Cache.memcached', $options);
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
    - `persist` is a boolean indicating whether to use a persistent connection, and will default to *true*.
    - `tls` is a boolean indicating whether to use a tls connection, and will default to *true*.
    - `ssl` is an array containing SSL options.
        - `key` is a string representing the path to the key file.
        - `cert` is a string representing the path to the certificate file.
        - `ca` is a string representing the path to the certificate authority file.

```php
$container->use(Config::class)->set('Cache.redis', $options);
```