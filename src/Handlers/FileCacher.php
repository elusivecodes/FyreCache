<?php
declare(strict_types=1);

namespace Fyre\Cache\Handlers;

use
    DirectoryIterator,
    Fyre\Cache\Cacher,
    Fyre\Cache\Exceptions\CacheException;

use const
    DIRECTORY_SEPARATOR,
    LOCK_EX;

use function
    chmod,
    fclose,
    filesize,
    flock,
    fopen,
    fseek,
    ftruncate,
    is_dir,
    is_file,
    is_numeric,
    mkdir,
    rmdir,
    rtrim,
    fwrite,
    stream_get_contents,
    strlen,
    strpos,
    substr,
    time,
    unlink,
    unserialize;

/**
 * FileCacher
 */
class FileCacher extends Cacher
{

    protected static array $defaults = [
        'path' => '/tmp/cache',
        'mode' => 0640
    ];

    /**
     * New Cacher constructor.
     * @param array $config Options for the handler.
     * @throws CacheException if the path is invalid.
     */
    public function __construct(array $config = [])
    {
        parent::__construct($config);

        $this->config['path'] = rtrim($this->config['path'], DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR;

        if (!is_dir($this->config['path']) && !mkdir($this->config['path'], 0777, true)) {
            throw CacheException::forInvalidPath($this->config['path']);
        }
    }

    /**
     * Delete an item from the cache.
     * @param string $key The cache key.
     * @return bool TRUE if the item was deleted, otherwise FALSE.
     */
    public function delete(string $key): bool
    {
        $key = $this->prepareKey($key);
        $filePath = $this->config['path'].$key;

        if (!is_file($filePath)) {
            return false;
        }

        unlink($filePath);

        return true;
    }

    /**
     * Empty the cache.
     * @return bool TRUE if the cache was cleared, otherwise FALSE.
     */
    public function empty(): bool
    {
        $files = $this->getFiles();

        foreach ($files AS $filePath) {
            @unlink($filePath);
        }

        return true;
	}

    /**
     * Retrieve a value from the cache.
     * @param string $key The cache key.
     * @return mixed The cache value.
     */
    public function get(string $key)
    {
        $fp = $this->openFile($key);

        $value = $this->readData($fp);

        $this->closeFile($fp);

        if ($value === null) {
            $this->delete($key);
        }

        return $value;
	}

    /**
     * Increment a cache value.
     * @param string $key The cache key.
     * @param int $amount The amount to increment.
     * @return int The new value.
     */
    public function increment(string $key, int $amount = 1): int
    {
        $fp = $this->openFile($key);

        $this->lockFile($fp);

        $value = $this->readData($fp);

        if (!is_numeric($value)) {
            $value = 0;
        }

        $value += $amount;

        $this->writeData($fp, $value);

        $this->closeFile($fp);

        return $value;
    }

    /**
     * Save an item in the cache.
     * @param string $key The cache key.
     * @param mixed $data The data to cache.
     * @param int|null $expire The number of seconds the value will be valid.
     * @return bool TRUE if the value was saved, otherwise FALSE.
     */
    public function save(string $key, $data, int|null $expire = null): bool
    {
        $fp = $this->openFile($key);

        $this->lockFile($fp);

        $this->writeData($fp, $data, $expire);

        $this->closeFile($fp);

        return true;
    }

    /**
     * Get the size of the cache.
     * @return int The size of the cache (in bytes).
     */
    public function size(): int
    {
        $files = $this->getFiles();

        $size = 0;

        foreach ($files AS $filePath) {
            $size += filesize($filePath);
        }

        return $size;
    }

    /**
     * Close a file pointer.
     * @param resource $fp The file pointer.
     */
    protected function closeFile($fp)
    {
        fclose($fp);
    }

    /**
     * Get the files contained in the cache path.
     * @return array The files.
     */
    protected function getFiles(): array
    {
        $contents = new DirectoryIterator($this->config['path']);

        $files = [];
        foreach ($contents AS $item) {
            if (!$item->isFile()) {
                continue;
            }

            if ($this->config['prefix'] && strpos($item->getFilename(), $this->config['prefix']) !== 0) {
                continue;
            }

            $files[] = $item->getPathname();
        }

        return $files;
    }

    /**
     * Lock a file pointer.
     * @param resource $fp The file pointer.
     */
    protected function lockFile($fp)
    {
        flock($fp, LOCK_EX);
    }

    /**
     * Open a cache file (or create it if it doesn't exist).
     * @param string $key The cache key.
     * @return resource The file pointer.
     */
    protected function openFile(string $key)
    {
        $key = $this->prepareKey($key);
        $filePath = $this->config['path'].$key;

        $dirname = dirname($filePath);

        if (!is_dir($dirname)) {
            mkdir($dirname, 0777, true);
        }

        $fp = fopen($filePath, 'c+');

        chmod($filePath, $this->config['mode']);

        return $fp;
    }

    /**
     * Read data from a file pointer.
     * @param resource The file pointer.
     * @return mixed The file data.
     */
    protected function readData($fp)
    {
        $contents = stream_get_contents($fp);

        if (!$contents) {
            return null;
        }

        $data = unserialize($contents);

        if ($data['expire'] && $data['expire'] <= time()) {
            return null;
        }

        return $data['data'];
    }

    /**
     * Write data to a file pointer.
     * @param resource The file pointer.
     * @param mixed $data The data to cache.
     * @param int|null $expire The number of seconds the value will be valid.
     */
    protected function writeData($fp, $data, int|null $expire = null)
    {
        ftruncate($fp, 0);
        fseek($fp, 0);

        $expire ??= $this->config['expire'];

        if ($expire) {
            $expire += time();
        }

        $data = serialize([
            'data' => $data,
            'expire' => $expire
        ]);

        $length = strlen($data);
        for ($written = 0; $written < $length; $written += $result) {
            $result = fwrite($fp, substr($data, $written));

            if ($result === false) {
                break;
            }
        }
    }

}
