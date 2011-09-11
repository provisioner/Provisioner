<?php

class Core {
    /**
     * Provides class auto-loading for Provisioner
     *
     * @throws  
     * @param   string  name of class
     * @return  bool
     */
    public static function autoload($class)
    {
        if (class_exists($class, FALSE))
        {
            return TRUE;
        }

		if(file_exists($class . '.php')) {
        	require_once $class . '.php';
		} else {
			return FALSE;
		}
    }
}

spl_autoload_register(array(
    'Core',
    'autoload'
));
