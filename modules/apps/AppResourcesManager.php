<?php


namespace Quantum;


class AppResourcesManager extends Singleton
{

    /**
     * @var ValueTree
     */
    private $scripts;

    /**
     * @var ValueTree
     */
    private $css_files;

    public function __construct()
    {
        $this->init();
    }

    public function init()
    {
        $this->scripts = new_vt();
        $this->css_files = new_vt();
    }


    public function addJavascriptFile($path)
    {
        $this->scripts->add($path);
    }

    public function addCssFile($path)
    {
        $this->css_files->add($path);
    }


    public function renderJavascriptFileTags()
    {
        $html = '';

        foreach ($this->scripts as $script)
        {
            $html .= '<script type="text/javascript" src="'.$script.'"></script>'.PHP_EOL;
        }

        return $html;
    }

    public function renderCssFileTags()
    {
        $html = '';

        foreach ($this->css_files as $css_file)
        {
            $html .= '<link rel="stylesheet" type="text/css" href="'.$css_file.'">'.PHP_EOL;
        }

        return $html;
    }


}