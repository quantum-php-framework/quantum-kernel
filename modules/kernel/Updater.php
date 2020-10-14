<?php


namespace Quantum;

define('QM_KERNEL_PROJECT_ID', 4);
define('QM_GITLAB_SERVER_API_URL', 'https://gitlab.quantum-framework.com/');

class Updater
{



    public function __construct()
    {
        $this->api_url = qurl(QM_GITLAB_SERVER_API_URL)->withPath('api/v4/');
    }

    public function updateKernel()
    {
        $local_version = QM_KERNEL_VERSION;
    }


    public function getReleases()
    {
        $url = $this->api_url
            ->withPath('projects/'.QM_KERNEL_PROJECT_ID.'/releases');


        $response = $url->getFileContents();

        dd($response);





        if ($response && is_string($response) && qs($response)->isJson())
        {
            $releases = \json_decode($response);

        }
    }
}