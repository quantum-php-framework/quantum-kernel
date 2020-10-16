<?php

namespace Quantum;

/**
 * Class Import
 * @package Quantum
 */
class Import {


    /**
     * Import constructor.
     */
    function __construct() {
   
    }

    /**
     * @param $uri
     */
    public static function library($uri) {
        $ipt = InternalPathResolver::getInstance();
        include_once($ipt->lib_root.$uri);
    }

    /**
     * @param $filter_name
     */
    public static function filter($filter_name) {
        $ipt = InternalPathResolver::getInstance();
        include_once($ipt->filters_root.$filter_name.'.php');
    }

    /**
     * @param $helper_name
     */
    public static function helper($helper_name) {
        $ipt = InternalPathResolver::getInstance();
        include_once($ipt->helpers_root.$helper_name.'.php');
    }

    /**
     * @param $view_name
     */
    public static function view($view_name) {
        $ipt = InternalPathResolver::getInstance();
        include_once($ipt->views_root.$view_name.'.tpl');
    }
    
    
    
   
    
    
    
}