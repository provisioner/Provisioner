<?php

class EndpointUi {
	public static $moduleDir = '';

	public static function findModules() {
		// This list will come from whatever directories exist inside $moduleDir that contain
		// We will scrape info from brand_data.xml in each directory

		return array('yealink');
	}

	public static function JsList() {
		// Return a list of JS files to include, relative paths to this file
		$js = array();

		$modules = self::findModules();

		foreach ($modules as $module) {
			$js[] = self::$moduleDir . $module . '/js/' . $module . '.js';
		}

		return $js;
	}

	public static function CssList() {
		// Return a list of CSS files to include, relative paths to this file
		$css = array();

                $modules = self::findModules();
                
                foreach ($modules as $module) {
                        $css[] = self::$moduleDir . $module . '/css/' . $module . '.css';
                }

                return $css;
	}
}

