<?php

require '../vendor/autoload.php';
use Desarrolla2\Cache\Cache as DCache;
use Desarrolla2\Cache\Adapter\File;

class Cache {

    protected $cacheDir = '/tmp/sourceCache';

    protected $Response;

    public function __construct()
    {
    }

    public function file($ttl = '3600')
    {
        $adapter = new File($this->cacheDir);
        $adapter->setOption('ttl', $ttl);
        $cache = new DCache($adapter);
        return $cache;
    } 



}
