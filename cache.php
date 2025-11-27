<?php

class Cache
{
    private $cache_path;
    private $cache_expire;

    public function __construct($exp_time = 3600, $path = "../cache/")
    {
        if (!file_exists($path)) mkdir($path, 0777, true);
        $this->cache_expire = $exp_time;
        $this->cache_path = $path;
    }

    private function fileName($key)
    {
        return $this->cache_path . md5($key);
    }

    public function put($key, $data)
    {
        $values = serialize($data);
        $filename = $this->fileName($key);
        $file = fopen($filename, 'w');
        
        if ($file) {
            fwrite($file, $values);
            fclose($file);
        } else {
            return false;
        }
    }

    public function get($key)
    {
        $filename = $this->fileName($key);

        if (!file_exists($filename) || !is_readable($filename)) {
            return false;
        }

        if ($this->cache_expire === 0 || time() < (filemtime($filename) + $this->cache_expire)) {
            $file = fopen($filename, "r");

            if ($file) {
                $data = fread($file, filesize($filename));
                fclose($file);
                return unserialize($data);
            } else {
                return false;
            }
        } else {
            return false;
        }
    }
}

