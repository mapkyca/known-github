<?php

/**
 * Support loading of direct checkout.
 */
spl_autoload_register(function($class) {
        $class = str_replace('\\', DIRECTORY_SEPARATOR, $class);

	$segments = explode(DIRECTORY_SEPARATOR, $class);
	$PLUGIN_NAME = $segments[1];

        $basedir = dirname(dirname(dirname(__FILE__))) . '/'; 
        $file = str_replace($PLUGIN_NAME, basename(dirname(__FILE__)) . "/$PLUGIN_NAME", $class);

	\Idno\Core\site()->plugins()->plugins[basename(dirname(__FILE__))] = \Idno\Core\site()->plugins()->plugins[$PLUGIN_NAME];
	unset(\Idno\Core\site()->plugins()->plugins[$PLUGIN_NAME]);

        if (file_exists($basedir . $file . '.php')) {
                include_once($basedir . $file . '.php');
        }

});


