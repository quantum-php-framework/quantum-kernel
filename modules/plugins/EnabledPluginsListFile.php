<?php

namespace Quantum\Plugins;

use Quantum\ValueTree;

class EnabledPluginsListFile
{

    /**
     * @var ValueTree
     */
    private $data;

    public function __construct($path)
    {
        $this->file = qf($path);
        $this->readData();
    }


    public function readData()
    {
        if ($this->file->existsAsFile()) {
            $this->data = new_vt(include $this->file->getRealPath());
        }
        else {
            $this->data = new_vt();
        }
    }

    public function addPlugin($class_name)
    {
        $this->data->set($class_name, 1);
        $this->save();;
    }

    public function removePlugin($class_name)
    {
        $this->data->remove($class_name);
        $this->save();;
    }

    public function save()
    {
        $data = $this->data->toStdArray();

        $contents = "<?php\n return ".var_export($data, true).";";

        $this->file->replaceContent($contents);
    }

    public function getList()
    {
        return $this->data->toStdArray();
    }

    public function getFile()
    {
        return $this->file;
    }

}