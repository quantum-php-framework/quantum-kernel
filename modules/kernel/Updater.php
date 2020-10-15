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

    private function downloadPackageToTmpDir($download_url)
    {
        $pkg_file = qs($download_url)->fromLastOccurrenceOf('/')->toStdString();

        $tmp_pkg_file = qf(InternalPathResolver::getInstance()->tmp_root)->getChildFile($pkg_file);

        $result = qurl($download_url)->downloadToFile($tmp_pkg_file);

        if ($result->failed()) {
            return $result;
        }

        return Result::ok('ok', $tmp_pkg_file);
    }

    public function updateKernel()
    {
        if (!$this->isKernelUpdateAvailable()) {
            return Result::fail('No update available');
        }

        $latest_release = $this->getLatestRelease();

        if (!isset($latest_release->assets->sources[0]->url)) {
            return Result::fail('Error parsing response: no package url found');
        }

        $download_url = $latest_release->assets->sources[0]->url;

        $download_result = $this->downloadPackageToTmpDir($download_url);

        if ($download_result->failed()) {
            return $download_result;
        }

        $tmp_pkg_file = $download_result->getData();

        $kernel_dir = qf(InternalPathResolver::getInstance()->kernel_root);

        $result = ZipFile::unzip($tmp_pkg_file->getRealPath(), $kernel_dir->getRealPath());

        if ($result->failed()) {
            return $result;
        }

        $extracted_pkg_dirname  = $tmp_pkg_file->getFileNameWithoutExtension();

        $new_pkg_dir = qf($kernel_dir)->getChildFile($extracted_pkg_dirname);

        if (!$new_pkg_dir->isDirectory()) {
            return Result::fail('unziped pkg not found'.$new_pkg_dir->getPath());
        }

        $old_pkg_dir = $kernel_dir->getChildFile('system');

        if ($old_pkg_dir->isDirectory()) {
            $old_pkg_dir->move($kernel_dir->getChildFile('system-'.qs(QM_KERNEL_VERSION)->slug()->toStdString())->getPath());
        }

        $new_pkg_dir = $new_pkg_dir->move($kernel_dir->getChildFile('system')->getPath());

        if ($new_pkg_dir->isDirectory())
        {
            $tmp_pkg_file->delete();
            return Result::ok();
        }

        return Result::fail();
    }

    public function getLatestRelease()
    {
        $releases = $this->getReleases();

        if (is_array($releases) && isset($releases[0])) {
            return $releases[0];
        }

        return null;

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