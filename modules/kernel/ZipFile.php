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

    public static function unzip($file, $to)
    {
        $z = new \ZipArchive();

        $zopen = $z->open( $file, \ZIPARCHIVE::CHECKCONS );
        if ( true !== $zopen ) {
            Result::fail('Incompatible file');
        }

        $tmp_dir = qf($to);

        for ( $i = 0; $i < $z->numFiles; $i++ ) {
            $info = $z->statIndex( $i );
            if ( ! $info ) {
                return Result::fail( 'Could not retrieve file from archive.' );
            }

            if ( '/' === substr( $info['name'], -1 ) ) { // Directory.
                continue;
            }

            if ( '__MACOSX/' === substr( $info['name'], 0, 9 ) ) { // Don't extract the OS X-created __MACOSX directory files.
                continue;
            }

            // Don't extract invalid files:
            if ( 0 !== validate_file( $info['name'] ) ) {
                continue;
            }

            $contents = $z->getFromIndex( $i );
            if ( false === $contents ) {
                return Result::fail( 'Could not extract file from archive.' .$info['name'] );
            }

            //dd($info['name']);
            $file = qf($tmp_dir.'/'.$info['name'])->replaceContent($contents);

            if ( ! $file->existsAsFile()) {
                return Result::fail(  'Could not copy file.'. $info['name'] );
            }

            $file->changeMode( 0644 & ~ umask() );
        }

        $z->close();

        return Result::ok('unzip_ok', $tmp_dir);

    }

}