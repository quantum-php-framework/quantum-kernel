<?php


namespace Quantum;

define('QM_KERNEL_PROJECT_ID', 4);
define('QM_GITLAB_SERVER_API_URL', 'https://gitlab.quantum-framework.com/');

class Updater
{


    public function __construct()
    {
        $this->releases = [];

        $this->api_url = qurl(QM_GITLAB_SERVER_API_URL)->withPath('api/v4/');
    }

    public function updateKernel()
    {
        if (!$this->isKernelUpdateAvailable()) {
            return Result::fail('No update available');
        }

        $releases = $this->getReleases();

        $latest_release = $releases[0];

        $download_url = $latest_release->assets->sources[0]->url;

        $file_name = qs($download_url)->fromLastOccurrenceOf('/')->toStdString();
        $file_dir = qs($file_name)->upToFirstOccurrenceOf('.zip')->toStdString();

        $tmp_pkg_file = qf(InternalPathResolver::getInstance()->tmp_root)->getChildFile($file_name);

        $result = qurl($download_url)->downloadToFile($tmp_pkg_file);

        if ($result->wasOk() && $tmp_pkg_file->existsAsFile())
        {
            $kernel_dir = qf(InternalPathResolver::getInstance()->kernel_root);

            $result = ZipFile::unzip($tmp_pkg_file->getRealPath(), $kernel_dir->getRealPath());

            if ($result->wasOk())
            {
                $new_kernel_dir = $kernel_dir->getChildFile($file_dir);

                if ($new_kernel_dir->isDirectory())
                {
                    $old_kernel_dir = $kernel_dir->getChildFile('system');

                    if ($old_kernel_dir->isDirectory()) {
                        $old_kernel_dir->move($kernel_dir->getChildFile('system-old')->getPath());
                    }
                    else {
                        return Result::fail('Unknown error');
                    }

                    if ($new_kernel_dir->move($kernel_dir->getChildFile('system')->getPath())->isDirectory()) {
                        return Result::ok();
                    }

                }
            }
        }

        return Result::fail('Unknown error');
    }


    public function getReleases()
    {
        if (empty($this->releases))
        {
            $url = $this->api_url
                ->withPath('projects/'.QM_KERNEL_PROJECT_ID.'/releases');

            $response = $url->readEntireTextStream();

            if ($response && is_string($response) && qs($response)->isJson()) {
                $this->releases = \json_decode($response);
            }
        }

        return $this->releases;
    }

    public function isKernelUpdateAvailable()
    {
        $releases = $this->getReleases();

        if (!empty($this->releases))
        {
            $latest_release = $releases[0];

            return (version_compare($latest_release->tag_name, QM_KERNEL_VERSION ) === 1);
        }

        return false;
    }
}