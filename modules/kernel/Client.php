<?php



namespace Quantum;
use Closure;

use Quantum\KernelSupport\MobileDetector;


/**
 * Contains method for accessing the client
 *
*/
class Client extends Singleton
{

    /**
     * Client constructor.
     */

    private $detector;

    function __construct()
    {

    }

    /**
     * @param $method
     * @param $args
     * @return mixed
     */
    public function __call($method, $args)
    {
        if (method_exists($this->detector, $method))
        {
            return call_user_func_array(array($this->detector, $method), $args);
        }

        trigger_error('Unknown function '.__CLASS__.':'.$method, E_USER_ERROR);

    }


    /**
     * @return bool
     */
    public function isDesktop()
    {
        $detector = $this->getDetector();

        if ($detector->isMobile() || $detector->isTablet())
            return false;

        return true;
    }


    /**
     * @return MobileDetector
     */
    public function getDetector()
    {
        if (!isset($this->detector))
        {
            $this->detector = new MobileDetector();
        }

        return $this->detector;
    }

    
    
    
}