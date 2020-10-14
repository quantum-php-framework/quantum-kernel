<?php



namespace Quantum;

use \PasswordPolicy\Policy;
use \PasswordPolicy\PolicyBuilder;
use \PasswordPolicy\Validator;


/**
 * Provides basic beforeFilter and afterFilter to be accessible for controllers.
 * This is loosely based on RoR before_filter and after_filter
*/
class PasswordPolicy
{


    /**
     * PasswordPolicy constructor.
     */
    function __construct() {
	
    }


    /**
     * @param $password
     * @return bool
     */
    public static function isValid($password)
    {
        $builder = new PolicyBuilder(new Policy);
        $builder->minLength(7)
            ->upperCase(1)
            ->lowerCase(1)
            ->digits(1)
            ->specialCharacters(1);
        $validator = new Validator($builder->getPolicy());

        return $validator->attempt($password);
    }

    /**
     * @return string
     */
    public static function getPolicyDescription()
    {
        return "Password should be at least 8 characters long and must contain uppercase, lowercase, numbers, and special characters";
    }
    
    
    
}