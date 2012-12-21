<?php

/*
 * This file is part of Twig.
 *
 * (c) 2009 Fabien Potencier
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Autoloads Twig classes.
 *
 * @package twig
 * @author  Fabien Potencier <fabien@symfony.com>
 */
class Twig_Autoloader
{
    /**
     * Registers Twig_Autoloader as an SPL autoloader.
     */
    static public function register()
    {
        ini_set('unserialize_callback_func', 'spl_autoload_call');
        spl_autoload_register(array(new self, 'autoload'));
    }

    /**
     * Handles autoloading of classes.
     *
     * @param string $class A class name.
     */
    static public function autoload($class)
    {
        if (0 !== strpos($class, 'Twig')) {
            return;
        }

        if (is_file($file = dirname(__FILE__).'/../'.str_replace(array('_', "\0"), array('/', ''), $class).'.php')) {
			/** Andrew
			file_put_contents('log',$file."\n",FILE_APPEND);
			copy($file, 'simple_twig/'.basename($file));
			$contents = file_get_contents($file);
			$contents = str_replace("<?php","",$contents);
			file_put_contents('simple_twig.php',$contents."\n",FILE_APPEND);
			**/
            require $file;
        }
    }
}
