<?php



namespace Quantum;

/**
 * Class Database
 * @package Quantum
 */
class Database
{
    /**
     * @var
     */
    public $db_name;
    /**
     * @var
     */
    public $db_host;
    /**
     * @var
     */
    public $db_user;
    /**
     * @var
     */
    public $db_password;

    /**
     * Database constructor.
     * @param $db_name
     * @param $db_host
     * @param $db_user
     * @param $db_password
     */
    function __construct($db_name, $db_host, $db_user, $db_password)
    {
        $this->db_name = $db_name;
        $this->db_host = $db_host;
        $this->db_user = $db_user;
        $this->db_password = $db_password;
    }
    

    
    
    
   
    
    
    
}