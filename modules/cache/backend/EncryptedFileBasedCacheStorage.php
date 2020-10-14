<?php

namespace Quantum\Cache\Backend;

use Quantum\Serialize\Serializer\Native;
use Quantum\SystemEncryptor;

/**
 * Class EncryptedFileBasedCacheStorage
 * @package Quantum\Cache
 */
class EncryptedFileBasedCacheStorage extends FilesBasedCacheStorage
{

    public function __construct()
    {
        parent::__construct();

        $this->setFileExtension('.enc');
        $this->setDirName('encrypted');
    }

    /**
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

        $data = SystemEncryptor::encrypt($data);

        $this->getFile($key)->writeLocked($data)->compress();

        return $var;

    }

    /**
     * @param $key
     * @return bool|string
     */
    public function get($key)
    {
        $file = self::getFile($key);

        if (!$file->existsAsFile())
            return false;

        $data = $file->getContentsDecompressed();

        if (!$data)
        {
            $file->delete();
            return false;
        }

        $data = SystemEncryptor::decrypt($data);

        if (!$data)
        {
            $file->delete();
            return false;
        }

        $data = Native::unserialize($data);

        if (!$data)
        {
            $file->delete();
            return false;
        }

        $t = time();

        if ($data[0] > 0 && $t > $data[0])
        {
            $file->delete();
            return false;
        }

        return $data[1];
    }
}