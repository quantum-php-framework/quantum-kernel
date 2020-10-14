<?php

namespace Quantum\Cache\Backend;

use Quantum\Cache\Backend;
use Quantum\InternalPathResolver;
use Quantum\File;
use Quantum\Serialize\Serializer\Native;

/**
 * Class Storage
 * @package Quantum\Redis
 */
class FilesBasedCacheStorage extends Backend
{

    /**
     * @var
     */
    private $file_extension;

    /**
     * @var
     */
    private $dir_name;

    /**
     * Storage constructor.
     * @throws \Exception
     */
    public function __construct()
    {
        $this->setFileExtension('.cache');
        $this->setDirName('regular');
    }

    /**
     * @param $key
     */
    public function getFile($key)
    {
        $filename = qs($key)->sha1()->append($this->getFileExtension());

        $dir = $this->getStorageDir()->getChildFile(qs($filename)->getFirstCharacter()->prepend('cache-'));

        if (!$dir->isDirectory())
            $dir->create();

        $file = $dir->getChildFile($filename);

        return $file;
    }

    /**
     * @return File
     */
    public function getStorageDir()
    {
        $ipt = InternalPathResolver::getInstance();
        $dir = File::newFile($ipt->getCacheRoot())->getChildFile($this->getDirName())->getChildFile(get_active_app_setting('name'));

        if (!$dir->isDirectory())
            $dir->create();

        return $dir;

    }

    /**
     * Defaults to one year if no expiration is set
     * @param $key
     * @param $var
     * @param int $expiration
     * @return mixed
     */
    public function set($key, $var, $expiration = 0)
    {
        if ($expiration > 0)
            $expiration = time() + $expiration;

        $data = array($expiration, $var);

        $data = Native::serialize($data);

        $this->getFile($key)->writeLocked($data)->compress();

        return $var;

    }

    /**
     * @param $key
     *
     * @return string
     */
    public function get($key)
    {
        $file = $this->getFile($key);

        if (!$file->exists())
            return false;

        $contents = $file->getContentsDecompressed();

        $data = Native::unserialize($contents);

        //dd($data);

        if (!$data)
        {
            $file->delete();
            return false;
        }

        $t = time();

        //expired time
        if ($data[0] > 0 && $t > $data[0])
        {
            $file->delete();
            return false;
        }

        return $data[1];
    }

    /**
     * @param $key
     * @return int
     */
    public function has($key)
    {
        $data = $this->get($key);

        return !empty($data);
    }

    /**
     * @return bool
     */
    public function flush()
    {
        $this->getStorageDir()->delete();

        return $this->getStorageDir()->create()->exists();
    }

    /**
     * @param $key
     * @param int $time
     * @return bool
     */
    public function delete($key)
    {
        $file = $this->getFile($key);

        if ($file->existsAsFile())
            $file->delete();

        return $file;
    }

    /**
     * @param $extension
     */
    public function setFileExtension($extension)
    {
        $this->file_extension = $extension;
    }

    /**
     * @return mixed
     */
    public function getFileExtension()
    {
        return $this->file_extension;
    }

    /**
     * @param $name
     */
    public function setDirName($name)
    {
        $this->dir_name = $name;
    }

    /**
     * @return mixed
     */
    public function getDirName()
    {
        return $this->dir_name;
    }

}