<?php

namespace CoonDesign\RateLimit;

/**
 * Uses Stash to cache data
 * See composer.json for required packages
 */
use Stash;

/**
 * Cache class
 */
class Cache {

    /**
     * Item Pool Cache
     * @var Stash\Pool
     */
    private Stash\Pool $pool;

    public function __construct() {
        $driver = new Stash\Driver\Redis();

        // TODO: Add more drivers and pool configurations
        $this->pool = new Stash\Pool($driver);
    }

    /**
     * Check if the key exists in the cache
     * @param string $key
     * @return bool
     */
    public function exists(string $key) {
        $item = $this->pool->getItem($key);
        return $item->isMiss() ? false : true;
    }

    /**
     * Get the value of the key from the cache
     * @param string $key
     * @return mixed
     */
    public function get($key) {
        $item = $this->pool->getItem($key);
        return $item->get();
    }

    /**
     * Set the value of the key in the cache
     * @param string $key
     * @param mixed $value
     * @param int $ttl
     */
    public function set($key, $value, $ttl) {
        $item = $this->pool->getItem($key);
        $item->set($value);
        $item->expiresAfter($ttl);
        $this->pool->save($item);
    }

    /**
     * Delete the key from the cache
     * @param string $key
     */
    public function delete($key) {
        $item = $this->pool->getItem($key);
        $item->clear();
    }

    /**
     * Clear the entire cache
     */
    public function clear() {
        $this->pool->clear();
    }

}