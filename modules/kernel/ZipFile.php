<?php

namespace Quantum;

/**
 * Class ZipFile
 * @package Quantum
 */
class ZipFile
{

    /**
     * @param $source
     * @param $target
     * @return bool
     */
    public static function zipFile($source, $target)
    {
        $zipfile = new File($target);
        $zipfile->create();

        $zip = new \ZipArchive;
        if ($zip->open($target, ZipArchive::CREATE) === TRUE)
        {
            $zip->addFile($source);
            $zip->close();
            return true;
        }

        return false;
    }


    /**
     * @param $source
     * @param $target
     * @return bool
     */
    public static function zipFolder($source, $target)
    {
        $zip = new \ZipArchive();

        if (!$zip->open($target, ZipArchive::CREATE | ZipArchive::OVERWRITE))
            return false;

        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($source),
            \RecursiveIteratorIterator::LEAVES_ONLY
        );

        foreach ($files as $name => $file)
        {
            // Skip directories (they would be added automatically)
            if (!$file->isDir())
            {
                $filePath = $file->getRealPath();
                $relativePath = substr($filePath, strlen($source) + 1);

                $zip->addFile($filePath, $relativePath);
            }
        }

        $zip->close();

        return true;
    }

}