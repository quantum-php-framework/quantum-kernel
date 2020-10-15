<?php

namespace Quantum;

use Quantum\Serialize\Serializer\Json;


/**
 * Class File
 * @package Quantum
 */
class File
{

    /**
     * @var mixed
     */
    public $path;
    /**
     * @var
     */
    public $contents;


    /**
     * File constructor.
     * @param QString $path
     */
    public function __construct($path = "")
    {
        if (is_object($path) && is_a($path, 'Quantum\File')) {
            $path = $path->getPath();
        }

        if (!is_string($path))
            trigger_error('path must be string');

        $this->path = self::removeDoubleSlashes($path);
    }

    /**
     * return $path
     */
    public function __toString()
    {
        return $this->path;
    }

    /**
     * @param $path
     */
    public function setPath($path)
    {
        $this->path = $path;

        return $this;
    }

    /**
     * @param $contents
     */
    public function replaceContent($contents)
    {
        $this->setContent($contents, true);

        return $this;
    }

    /**
     * @param $contents
     * @param bool $save
     */
    public function setContent($contents, $save = false)
    {
        $this->contents = $contents;

        if ($save)
            $this->save();

        return $this;
    }

    /**
     * @param QString $contents
     * @return $this
     */
    public function create($contents = "")
    {
        if (!pathinfo($this->path, PATHINFO_EXTENSION)) {
            $this->createDirIfNeeded($this->path);
            return $this;
        }

        if ($this->exists())
        {
            $this->replaceContent($contents);
            return $this;
        }

        if (!is_dir($this->getParentDirPath()))
            $this->createDirIfNeeded($this->getParentDirPath());

        if (!$handle = fopen($this->path, 'w')) {
            trigger_error("Cannot open file ($this->path)");
        }

        if (fwrite($handle, $contents) === FALSE) {
            trigger_error("Cannot write to file ($this->path)");
        }

        fclose($handle);

        return $this;
    }

    /**
     *
     */
    public function save()
    {
        if (!file_exists($this->path))
            $this->create();

        if (!$this->isDirectory())
            file_put_contents($this->path, $this->contents, LOCK_EX);

        return $this;
    }

    /**
     * @return bool|QString
     */
    public function load()
    {
        if (!$this->hasBeenLoaded())
        {
            return $this->reload();
        }

        return $this->contents;
    }

    /**
     * @return bool
     */
    public function hasBeenLoaded()
    {
        return !empty($this->contents);
    }

    /**
     * @return bool|QString
     */
    public function getContents()
    {
        return $this->reload();
    }

    /**
     * @return bool
     */
    public function delete()
    {
        if (!$this->exists())
            return false;

        static::deleteAllKindOfFiles($this->path);
    }

    /**
     * @return mixed
     */
    public function info()
    {
        return pathinfo($this->getRealPath());
    }

    /**
     * @return QString
     */
    public function getParentDirPath()
    {
        return \dirname($this->path);
    }

    /**
     * @param $path
     * @return bool
     */
    public static function createDirIfNeeded($path)
    {
        if (is_dir($path))
            return true;

        $prev_path = substr($path, 0, strrpos($path, '/', -2) + 1);
        $return = self::createDirIfNeeded($prev_path);
        $result = ($return && is_writable($prev_path)) ? mkdir($path) : false;

        return $result;
    }

    /**
     * @return mixed
     */
    public function getExtension()
    {
        $info = $this->info();

        return $info['extension'];
    }

    /**
     * @return mixed
     */
    public function getFileNameWithoutExtension()
    {
        $info = $this->info();

        return $info['filename'];
    }

    /**
     * @return mixed
     */
    public function getFileName()
    {
        $info = $this->info();

        return $info['basename'];
    }

    /**
     * @return mixed
     */
    public function getParentDirectory()
    {
        $info = $this->info();

        return $info['dirname'];
    }

    /**
     * @return mixed
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * @return int
     */
    public function getSize()
    {
        if ($this->isDirectory())
            return $this->getDirectorySize();

        return $this->getFileSize();
    }

    /**
     * @return QString
     */
    public function getFormattedSize()
    {
        return Utilities::formatBytes($this->getSize());
    }

    /**
     * @return QString
     */
    public function getMD5()
    {
        return md5_file($this->path);
    }

    /**
     * @return QString
     */
    public function getSHA1()
    {
        return sha1_file($this->path);
    }

    /**
     * @param QString $algo
     * @return QString
     */
    public function hash($algo = "sha256")
    {
        return hash_file($algo, $this->path);
    }

    /**
     * @return bool|QString
     */
    public function getRealPath()
    {
        return realpath($this->path);
    }

    /**
     * @param $filename
     * @return File
     */
    public function getChildFile($filename)
    {
        $path = $this->getPath() . $this->getSeparatorString() . $filename;

        return new File($path);
    }

    /**
     * @param $filename
     * @return File
     */
    public function getSiblingFile($filename)
    {
        $parentDirectory = new File($this->getParentDirectory());

        return $parentDirectory->getChildFile ($filename);
    }

    /**
     * @param $potentialParentDirectory
     * @return bool
     */
    public function isAChildOf($potentialParentDirectory)
    {
        if (empty($potentialParentDirectory))
            return false;

        $ourPath = $this->getPathUpToLastSlash();

        if (strcmp($potentialParentDirectory, $ourPath) == 0)
            return true;

        if (strlen($potentialParentDirectory) >= strlen($this->path))
            return false;

        $parent_dir = new File($this->getParentDirPath());

        return $parent_dir->isAChildOf($potentialParentDirectory);
    }

    /**
     * @return QString
     */
    public static function getSeparatorString()
    {
        return "/";
    }

    /**
     * @return QString
     */
    public static function getSeparatorChar()
    {
        return '/';
    }

    /**
     * @return bool|mixed|QString
     */
    public function getPathUpToLastSlash()
    {
        $lastSlash = strrpos($this->path, self::getSeparatorString());

        if ($lastSlash > 0)
            return \substr($this->path, 0, $lastSlash);

        if ($lastSlash == 0)
            return $this->getSeparatorString();

        return $this->path;
    }

    /**
     * @return bool
     */
    public function hasWriteAccess()
    {
        return is_writable($this->path);
    }

    /**
     * @param $mode
     */
    public function changeMode($mode)
    {
        chmod($this->path, $mode);

        return $this;
    }

    /**
     * @param $shouldBeReadOnly
     */
    public function setReadOnly($shouldBeReadOnly)
    {
        if ($shouldBeReadOnly)
            $this->changeMode(0444);
        else
            $this->changeMode(0644);

        return $this;
    }

    /**
     *
     */
    public function setForPublicWriteAccess()
    {
        $this->changeMode(0666);

        return $this;
    }

    /**
     * @return array
     */
    public function getStat()
    {
        return stat($this->path);
    }

    /**
     * @return mixed
     */
    public function getOwnerId()
    {
        return $this->getStat()[4];
    }

    /**
     * @return mixed
     */
    public function getLastAccessTimeStamp()
    {
        return $this->getStat()['atime'];
    }

    /**
     * @return mixed
     */
    public function getLastModificationTimeStamp()
    {
        return $this->getStat()['mtime'];
    }

    /**
     * @return mixed
     */
    public function getLastInodeChangeTimeStamp()
    {
        return $this->getStat()['ctime'];
    }

    /**
     * @return false|QString
     */
    public function getLastAccessDateTime()
    {
        return \to_datestamp($this->getLastAccessTimeStamp());
    }

    /**
     * @return false|QString
     */
    public function getLastModificationDateTime()
    {
        return \to_datestamp($this->getLastModificationTimeStamp());
    }

    /**
     * @return false|QString
     */
    public function getLastInodeChangeDateTime()
    {
        return \to_datestamp($this->getLastInodeChangeTimeStamp());
    }

    /**
     * @return array
     */
    public function getOwner()
    {
        return posix_getpwuid(fileowner($this->path));
    }

    /**
     * @return mixed
     */
    public function getOwnerName()
    {
        return $this->getOwner()['name'];
    }

    /**
     * @return QString
     */
    public function getFileSystemRoot()
    {
        return "/";
    }

    /**
     * @param QString $algo
     * @return QString
     */
    public function getFileIdentifier($algo  = "sha1")
    {
        return hash($algo, $this->path);
    }

    /**
     * @return QString
     */
    public function getUniqueReplacementFileName()
    {
        return concat($this->getFileIdentifier(), $this->getExtension());
    }

    /**
     * @param QString $algo
     * @return QString
     */
    public function getContentsHash($algo = "sha256")
    {
        return hash($algo, $this->getContents());
    }

    /**
     * @param $source
     * @param $dest
     * @return bool
     */
    public static function copyAllKindOfFiles($source, $dest)
    {
        if (is_link($source))
        {
            return symlink(readlink($source), $dest);
        }
        if (is_file($source))
        {
            return copy($source, $dest);
        }
        if (!is_dir($dest))
        {
            static::createDirIfNeeded($dest);
        }

        $dir = dir($source);

        while (false !== $entry = $dir->read())
        {
            if ($entry == '.' || $entry == '..')
            {
                continue;
            }

            static::copyAllKindOfFiles("$source/$entry", "$dest/$entry");
        }

        $dir->close();

        return true;
    }

    /**
     * @param $path
     * @return bool
     */
    public static function deleteAllKindOfFiles($path)
    {
        if (is_file($path) || is_link($path))
            return unlink($path);
        
        if (!is_dir($path))
            return false;
        
        $dir = dir($path);
        while (false !== $entry = $dir->read()) 
        {
            if ($entry == '.' || $entry == '..') 
            {
                continue;
            }
            $item = $path . self::getSeparatorString() . $entry;
            if (is_dir($item))
            {
                static::deleteAllKindOfFiles($item);
            }
            else {
                unlink($item);
            }
        }
        
        return rmdir($path);
    }

    /**
     * @param $dest
     * @return bool
     */
    public function copy($dest)
    {
        $source = $this->getPath();
        return static::copyAllKindOfFiles($source, $dest);

    }

    /**
     * @param $target
     * @return File
     */
    public function move($target)
    {
        if ($this->rename($target)) {
            return new File($target);
        }

        return false;
    }

    /**
     * @param $target
     * @return File
     */
    public function rename($target)
    {
        return rename($this->path, $target);
    }

    /**
     * @return bool
     */
    public function touch()
    {
        return \touch($this->path);
    }

    /**
     * @return bool
     */
    public function isExecutable()
    {
        return is_executable($this->path);
    }

    /**
     * @return bool
     */
    public function isSymbolicLink()
    {
        return is_link($this->path);
    }

    /**
     * @return bool
     */
    public function isFile()
    {
        return is_file($this->path);
    }

    /**
     * @return bool
     */
    public function isDirectory()
    {
        return is_dir($this->path);
    }

    /**
     * @return bool
     */
    public function isUploadedFile()
    {
        return is_uploaded_file($this->path);
    }

    /**
     * @return bool
     */
    public function exists()
    {
        return file_exists($this->path);
    }

    /**
     * @return bool
     */
    public function existsAsFile()
    {
        return ($this->exists() && $this->isFile());
    }

    /**
     * @return bool
     */
    public function isHidden()
    {
        return starts_with($this->getFileName(), ".");
    }

    /**
     * @return bool|QString
     */
    public function reload()
    {
        $contents = \file_get_contents($this->path);

        if ($contents)
            $this->contents = $contents;

        return $this->contents;
    }

    /**
     * @return array|bool
     */
    public function loadAsArray()
    {
        $contents = \file($this->path);

        if ($contents)
            $this->contents = $contents;

        return $this->contents;
    }

    /**
     * @return bool|QString
     */
    public function loadAsString()
    {
        return $this->load();
    }

    /**
     * @return array
     */
    public function loadAsCsv()
    {
        $csvFile = file($this->path);
        $data = [];
        foreach ($csvFile as $line) {
            $data[] = str_getcsv($line);
        }
        return $data;
    }

    /**
     * @return mixed
     */
    public function loadAsJson()
    {
        return Json::unserialize($this->load());
    }

    /**
     * @return QString
     */
    static function getCurrentWorkingDirectory()
    {
        return getcwd();
    }

    /**
     *
     */
    function encryptWithSystemEncryptor()
    {
        $this->reload();
        $this->replaceContent(\Quantum\SystemEncryptor::encrypt($this->contents));

        return $this;
    }

    /**
     *
     */
    function decryptWithSystemEncryptor()
    {
        $this->reload();
        $this->replaceContent(\Quantum\SystemEncryptor::decrypt($this->contents));

        return $this;
    }

    /**
     * @param $key
     */
    function encrypt($key)
    {
        $this->reload();
        $this->replaceContent(\Quantum\Crypto::encrypt($this->contents, $key));

        return $this;
    }

    /**
     * @param $key
     */
    function decrypt($key)
    {
        $this->reload();
        $this->replaceContent(\Quantum\Crypto::decrypt($this->contents, $key));

        return $this;
    }

    /**
     * @param $target
     * @return bool
     */
    function createZip($target)
    {
        if ($this->isFile())
        {
            return \Quantum\ZipFile::zipFile($this->getPath(), $target);
        }
        else if ($this->isDirectory())
        {
            return \Quantum\ZipFile::zipFolder($this->getPath(), $target);
        }

        return false;
    }

    /**
     * @return array|bool
     */
    public function getChildFiles()
    {
        if (!$this->isDirectory())
            return false;

        $files = $this->scandir();

        if (!$files)
            return false;

        foreach ($files as $key => $file)
        {
            $files[$key] = new File($this->getChildFile($file)->getRealPath());
        }

        return $files;
    }

    /**
     * @return bool|int
     */
    public function getNumChildFiles()
    {
        if (!$this->isDirectory())
            return false;

        $child_files = $this->getChildFiles();

        if (!empty($child_files))
            return count($child_files);

        return 0;
    }

    /**
     * @return bool
     */
    public function containsSubDirectories()
    {
        if (!$this->isDirectory())
            return false;

        $child_files = $this->getChildFiles();

        foreach ($child_files as $child_file)
        {
            if ($child_file->isDirectory())
                return true;
        }

        return false;
    }

    /**
     * @param $textToAppend
     */
    public function appendText($textToAppend)
    {
        file_put_contents($this->path, $textToAppend.PHP_EOL , FILE_APPEND | LOCK_EX);
    }

    /**
     * @param $target
     * @return int
     */
    public function hasIdenticalContentTo($target)
    {
        $local_contents = $this->load();

        $targetFile = new File($target);
        $target_contents = $targetFile->load();

        return strcmp(sha1($local_contents), sha1($target_contents));
    }

    /**
     * @param $filename
     * @param $contents
     * @return File
     */
    public static function createTempFile($filename, $contents = "")
    {
        $dir = new File(InternalPathResolver::getInstance()->tmp_root);
        $file = $dir->getChildFile($filename);
        $file->create();

        if (!empty($contents))
            $file->replaceContent($contents);

        return $file;
    }

    /**
     *
     */
    public function setLuckySevenPermissions()
    {
        $this->changeMode(0777);

        return $this;
    }

    /**
     *
     */
    public function setAsExecutable()
    {
        $exec = new Exec("chmod +x ".$this->path);
        $exec->launch();

        return $this;
    }

    /**
     * @return mixed
     */
    public function execute()
    {
        $this->setAsExecutable();
        $command = ".".self::getSeparatorString().$this->path;

        $exec = new Exec($command);
        $exec->launch();

        return $exec->getResultCode();
    }

    /**
     * @return int
     */
    public function getDirectorySize()
    {
        $dir = $this->path;

        $handle = opendir($dir);

        $mas = 0;

        while ($file = readdir($handle))
        {
            if ($file != '..' && $file != '.' && !is_dir($dir.'/'.$file))
            {
                $mas += filesize($dir.'/'.$file);
            }
            else if (is_dir($dir.'/'.$file) && $file != '..' && $file != '.') {
                $mas += $this->getDirectorySize($dir.$this->getSeparatorString().$file);
            }
        }
        return $mas;
    }

    /**
     * @return int
     */
    public function getFileSize()
    {
        return filesize($this->path);
    }

    /**
     * Send a file to browser through Output->pushFile
     */
    public function pushToClient()
    {
        Output::getInstance()->pushFile($this->path);

        return $this;
    }

    /**
     * @param $string
     * @return bool
     */
    public function hasText($string)
    {
        $contents = $this->load();

        if (Utilities::stringContainsIgnoreCase($contents, $string))
            return true;

        return false;
    }

    /**
     * @param $filename
     * @return bool|File
     */
    public static function createFromUploadedFile($filename)
    {
        if (is_uploaded_file($_FILES[$filename]['tmp_name']))
        {
            return new File($_FILES[$filename]['tmp_name']);
        }

        return false;
    }

    /**
     * @return bool
     */
    public function lock()
    {
        $fp = fopen($this->path, "r+");

        if (flock($fp, LOCK_EX))
            return true;

        return false;
    }

    /**
     * @return bool
     */
    public function unlock()
    {
        $fp = fopen($this->path, "r+");

        if (flock($fp, LOCK_UN))
            return true;

        return false;
    }

    /**
     * @param $target
     * @return array|bool
     */
    public function createSymbolicLink($target)
    {
        symlink($target, $this->getPath());

        return File($target);
    }

    /**
     * @return QString
     */
    public function getLinkedTarget()
    {
        return readlink($this->path);
    }


    /**
     * @param $path
     * @return QString
     */
    public static function addTrailingSeparator($path)
    {
        if (!ends_with($path, self::getSeparatorString()))
            return concat($path, self::getSeparatorString());
    }

    /**
     * @return bool
     */
    public function isAbsolutePath()
    {
        if (starts_with($this->path, self::getSeparatorChar()) || starts_with($this->path, '~'))
            return true;

        return false;
    }

    /**
     * @param $original
     * @return QString
     */
    public static function createLegalPathName($original)
    {
        $s = $original;
        $start = "";

        if (!empty($s) && starts_with($s, ':'))
        {
            $start = substr($s, 0, 2);
            $s = substr($s, 2);
        }

        $new_str  = substr (preg_replace('\"#@,;:<>*^|?', '', $s), 0, 1024);

        return concat($start, $new_str);
    }

    /**
     * @param $original
     * @return bool|null|QString|QString[]
     */
    public static function createLegalFileName ($original)
    {
        $s = preg_replace ('\"#@,;:<>*^|?\/', "", $original);

        $maxLength = 128; // only the length of the filename, not the whole path
        $len = strlen($s);

        if ($len > $maxLength)
        {
            $lastDot = strrpos($s, ".");

            if ($lastDot > max (0, $len - 12))
            {
                $s = substr($s, 0, $maxLength - ($len - $lastDot)) . substr($s , $lastDot);
                //$s = s.substring (0, maxLength - (len - lastDot))
                  //   + s.substring (lastDot);
            }
            else
            {
                $s = substr($s, 0 , $maxLength);
            }
        }

        return $s;
    }

    /**
     * @param $path
     * @return mixed
     */
    public static function removeDoubleSlashes($path)
    {
        if (str_contains($path, "//"))
            $path = str_replace("//", "/", $path);

        return $path;
    }

    /**
     * @param $to
     * @return QString
     */
    public function getRelativePathFrom($to)
    {
        $from = $this->path;

        $ps = self::getSeparatorChar();

        $arFrom = explode($ps, rtrim($from, $ps));
        $arTo = explode($ps, rtrim($to, $ps));

        while(count($arFrom) && count($arTo) && ($arFrom[0] == $arTo[0]))
        {
            array_shift($arFrom);
            array_shift($arTo);
        }

        return str_pad("", count($arFrom) * 3, '..'.$ps).implode($ps, $arTo);
    }

    /**
     * @return bool|mixed
     */
    public function getFirstSubdirectory()
    {
        $files = $this->getChildFiles();

        foreach ($files as $file)
        {
            if ($file->isDirectory())
                return $file;
        }

        return false;
    }

    /**
     * @return array|bool
     */
    public function getSubDirectories()
    {
        if (!$this->containsSubDirectories())
            return false;

        $dirs = array();
        $files = $this->getChildFiles();

        foreach ($files as $file)
        {
            if ($file->isDirectory())
                array_push($dirs, $file);
        }

        return $dirs;

    }

    /**
     * @param $fileName
     * @return bool
     */
    public function hasChildFile($fileName)
    {
        $files = $this->getChildFiles();

        if (empty($files))
            return false;

        foreach ($files as $file)
        {
            if ($file->getFileName() == $fileName)
               return true;
        }

        return false;
    }

    /**
     * @return array|bool
     */
    public function scandir()
    {
        $files = \scandir($this->path);

        if (!$files)
            return false;

        $real_files = array();

        foreach ($files as $file)
        {
            if ($file != '.' && $file != '..')
                array_push($real_files, $file);
        }

        return $real_files;

    }

    /**
     * @param $filename
     * @return bool
     */
    public function findFileInChildDirectories($filename)
    {
        $dirs = $this->getSubDirectories();

        foreach ($dirs as $dir)
        {
            if ($dir->hasChildFile($filename))
                return $dir->getChildFile($filename);
        }

        return false;
    }

    /**
     * @return bool
     */
    public function isDirectoryEmpty()
    {
        return $this->getNumChildFiles() == 0;
    }

    /**
     * @param $filename
     */
    public function deleteAllChildFilesExcept($filename)
    {
        $files = $this->getChildFiles();

        foreach ($files as $file)
        {
            if ($file->isDirectory())
            {
                $file->deleteAllChildFilesExcept($filename);

                if ($file->isDirectoryEmpty())
                {
                    $file->delete();
                }
            }
            else
            {
                if ($file->getFileName() != $filename)
                    $file->delete();
            }
        }
    }

    /**
     * @param $path
     * @return File
     */
    public static function newFile($path)
    {
        return new File($path);
    }


    /**
     * Recursive get all subdirectories,
     * unlike getSubdirectories() it uses
     * deepscan() to recursively read
     * all children dirs.
     * @return array
     */
    public function getAllSubDirectories()
    {
        return deepscan($this->path);
    }


    /**
     * Writes with a LOCK_EX
     * @param $contents
     * @param $contents
     * @throws \Exception
     */
    public function writeLocked($contents)
    {
        $handler = fopen($this->getPath(), 'a+');
        if (!$handler)
            throw new \Exception('Could not write to file:'.$this->getPath());

        $lock = flock($handler, LOCK_EX);

        if (!$lock) {
            fclose($handler);
            return false;
        }

        fseek($handler, 0);

        ftruncate($handler, 0);

        if (fwrite($handler, $contents) === false)
            throw new \Exception('Could not write to file:'.$this->getPath());

        flock($handler, LOCK_UN);

        fclose($handler);

        return $this;


        //return file_put_contents($this->path, $contents, LOCK_EX);
    }


    /**
     * Reads with a LOCK_SH
     * @return bool|false|string
     */
    public function readLocked()
    {
        $filename = $this->getPath();

        if (!file_exists($filename))
            return false;

        $h = fopen($filename,'r');

        if (!$h)
            return false;

        flock($h, LOCK_SH);

        $data = file_get_contents($filename);
        fclose($h);

        return $data;
    }


    /**
     * @return string
     * @throws \Defuse\Crypto\Exception\BadFormatException
     * @throws \Defuse\Crypto\Exception\EnvironmentIsBrokenException
     */
    function getContentsEncryptedWithSystemEncryptor()
    {
        return SystemEncryptor::decrypt($this->readLocked());
    }


    /**
     * @return $this
     * @throws \Exception
     */
    function compress()
    {
        $contents = @gzcompress($this->readLocked());

        $this->writeLocked($contents);

        return $this;
    }

    /**
     * @return string
     */
    function getContentsDecompressed()
    {
        return @gzuncompress($this->readLocked());
    }


    /**
     * @return string
     */
    function getMimeContentType()
    {
        return mime_content_type($this->path);
    }






}