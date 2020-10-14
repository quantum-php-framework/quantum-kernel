<?php


namespace Quantum\Plugins;


class PluginFolder extends \Quantum\File
{
    private $plugin_entry_headers;

    public function __construct($path)
    {
        parent::__construct($path->path);
    }

    public function getPluginEntryFile()
    {
        $files = $this->getChildFiles();

        foreach ($files as $file)
        {
            $headers = $this->readFileHeaders($file);

            if (!empty($headers['name'])) {

                $this->plugin_entry_headers = $headers;
                return $file->getRealPath();
            }
        }

        return false;
    }

    public function isValid()
    {
        $entry_file = $this->getPluginEntryFile();

        return ($entry_file != false && qf($entry_file)->existsAsFile());
    }

    public function getPluginEntryHeader($key, $fallback = false)
    {
        $headers = new_vt($this->getPluginEntryHeaders());

        return $headers->get($key, $fallback);
    }


    public function getPluginEntryHeaders()
    {
        if (isset($this->plugin_entry_headers)) {
            return $this->plugin_entry_headers;
        }

        $file = $this->getPluginEntryFile();

        return $this->plugin_entry_headers;
    }

    public function getScope()
    {
        if (qs($this->path)->contains('kernel/plugins')) {
            return 'kernel';
        }

        if (qs($this->path)->contains('shared/plugins')) {
            return 'shared-apps';
        }

        return 'app';
    }

    public function getLoadedByStatus()
    {
        if ($this->canPluginBeLoadedByKernel()) {
            return 'kernel';
        }

        if ($this->canPluginBeLoadedByActiveApp()) {
            return 'app';
        }

        return 'disabled';
    }

    public function getEntryClassFromEntryFileHeaders()
    {
        return $this->getPluginEntryHeader('entry_class');
    }

    public function canPluginBeLoadedByKernel()
    {
        $entry_class = $this->getEntryClassFromEntryFileHeaders();

        $config = \Quantum\Config::getInstance();

        $enabled_plugins = new_vt($config->getEnabledKernelPlugins());

        return $enabled_plugins->hasEqualParam($entry_class, 1);
    }

    public function canPluginBeLoadedByActiveApp()
    {
        $entry_class = $this->getEntryClassFromEntryFileHeaders();

        $config = \Quantum\Config::getInstance();

        $enabled_plugins = new_vt($config->getEnabledActiveAppPlugins());

        return $enabled_plugins->hasEqualParam($entry_class, 1);
    }

    public function readFileHeaders($file)
    {
        $default_headers = array(
            'name'        => 'Plugin Name',
            'resource_url'   => 'Plugin URI',
            'version'     => 'Version',
            'description' => 'Description',
            'author'      => 'Author',
            'author_url'   => 'Author URI',
            'text_domain'  => 'Text Domain',
            'domain_path'  => 'Domain Path',
            'network'     => 'Network',
            'min_qm_version'  => 'Requires at least',
            'min_php_version' => 'Requires PHP',
            'namespace' => 'Namespace',
            'entry_class' => 'Entry Class'
        );

        $fp = fopen( $file, 'r' );

        $file_data = fread( $fp, 8 * 1024);

        fclose( $fp );

        $file_data = str_replace( "\r", "\n", $file_data );

        $all_headers = $default_headers;

        foreach ( $all_headers as $field => $regex ) {
            if ( preg_match( '/^[ \t\/*#@]*' . preg_quote( $regex, '/' ) . ':(.*)$/mi', $file_data, $match ) && $match[1] ) {
                $all_headers[ $field ] = $this->cleanupHeaderComment( $match[1] );
            } else {
                $all_headers[ $field ] = '';
            }
        }

        return $all_headers;
    }

    private function cleanupHeaderComment( $str )
    {
        return trim( preg_replace( '/\s*(?:\*\/|\?>).*/', '', $str ) );
    }



}