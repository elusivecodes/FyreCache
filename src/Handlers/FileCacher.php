<?php
declare(strict_types=1);

namespace Fyre\Cache\Handlers;

use
    Fyre\Cache\Cacher,
    Fyre\FileSystem\Exceptions\FileSystemException,
    Fyre\FileSystem\File,
    Fyre\FileSystem\Folder,
    Fyre\Utility\Path;

use function
    is_numeric,
    serialize,
    time,
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

    protected Folder $folder;

    /**
     * New Cacher constructor.
     * @param array $options Options for the handler.
     */
    public function __construct(array $options = [])
    {
        parent::__construct($options);

        $this->folder = new Folder($this->config['path'], true);
    }

    /**
     * Delete an item from the cache.
     * @param string $key The cache key.
     * @return bool TRUE if the item was deleted, otherwise FALSE.
     */
    public function delete(string $key): bool
    {
        $key = $this->prepareKey($key);
        $filePath = Path::join($this->folder->path(), $key);

        $file = new File($filePath);

        if (!$file->exists()) {
            return false;
        }

        $file->delete();

        return true;
    }

    /**
     * Empty the cache.
     * @return bool TRUE if the cache was cleared, otherwise FALSE.
     */
    public function empty(): bool
    {
        $this->folder->empty();

        return true;
	}

    /**
     * Retrieve a value from the cache.
     * @param string $key The cache key.
     * @return mixed The cache value.
     */
    public function get(string $key)
    {
        $file = $this->getFile($key);

        $file->open('r');

        $value = $this->readData($file);

        $file->close();

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
        $file = $this->getFile($key);

        $file->open('c');
        $file->lock();

        $value = $this->readData($file);

        if (!is_numeric($value)) {
            $value = 0;
        }

        $value += $amount;

        $file->truncate();
        $file->rewind();

        $this->writeData($file, $value);

        $file->close();

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
        $file = $this->getFile($key);

        $file->open('w');
        $file->lock();

        $this->writeData($file, $data, $expire);

        $file->close();

        return true;
    }

    /**
     * Get the size of the cache.
     * @return int The size of the cache (in bytes).
     */
    public function size(): int
    {
        return $this->folder->size();
    }

    /**
     * Open a cache file (or create it if it doesn't exist).
     * @param string $key The cache key.
     * @return File The File.
     */
    protected function getFile(string $key): File
    {
        $key = $this->prepareKey($key);
        $filePath = Path::join($this->folder->path(), $key);

        $file = new File($filePath, true);

        $file->chmod($this->config['mode']);

        return $file;
    }

    /**
     * Read data from a File.
     * @param File $file The File.
     * @return mixed The file data.
     */
    protected function readData(File $file)
    {
        try {
            $contents = $file->contents();

            if (!$contents) {
                return null;
            }

            $data = unserialize($contents);

            if ($data['expire'] && $data['expire'] <= time()) {
                return null;
            }

            return $data['data'];
        } catch (FileSystemException $e) {
            return null;
        }
    }

    /**
     * Write data to a file pointer.
     * @param File $file The File.
     * @param mixed $data The data to cache.
     * @param int|null $expire The number of seconds the value will be valid.
     */
    protected function writeData(File $file, $data, int|null $expire = null): void
    {
        $expire ??= $this->config['expire'];

        if ($expire) {
            $expire += time();
        }

        $data = serialize([
            'data' => $data,
            'expire' => $expire
        ]);

        $file->write($data);
    }

}
