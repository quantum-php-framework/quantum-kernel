<?php

/*
 * class {$controller_name}
 */

class {$controller_name} extends Quantum\Controller
{
    
    /**
     * Create a controller, no dependency injection has happened.
    */
	function __construct()
	{

	}

    /**
     * Called after dependency injection, all environment variables are ready.
    */
	protected function __post_construct()
	{

	}

    /**
     * Called before calling the main controller action, all environment variables are ready.
    */
	protected function __pre_dispatch()
	{

	}

    /**
     * Called after calling the main controller action, all vars set by controller are ready.
    */
	protected function __post_dispatch()
	{

	}

	/**
     * Called after calling the main controller action, before calling Quantum\Output::render
    */
	protected function __pre_render()
	{

	}

	/**
     * Called after calling Quantum\Output::render
    */
	protected function __post_render()
	{

	}


	/**
     * Public: index
    */
    public function index()
    {
      
      
    }
    
    {if isset($public_functions)}
    {foreach from=$public_functions item=public_function}
    
    /**
     * Public: {$public_function}
    */
    public function {$public_function}()
    {
      
    }
    
    {/foreach}
    {/if}
    
    {if isset($private_functions)}
    {foreach from=$private_functions item=private_function}
    
    /**
     * Private: {$private_function}
    */
    private function {$private_function}()
    {
      
    }
    
    {/foreach}
    {/if}
    
    {if isset($protected_functions)}
    {foreach from=$protected_functions item=protected_function}
    
    /**
     * Protected: {$protected_function}
    */
    protected function {$protected_function}()
    {
      
    }
    
    {/foreach}
    {/if} 
    
}

?>