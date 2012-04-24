<?php
/**
 *
 */
use lithium\core\Libraries;
use lithium\storage\Cache;
use lithium\net\http\Media;
use lithium\action\Dispatcher;

// Plugin Location
defined('FRONTENDER_PATH') OR define('FRONTENDER_PATH', dirname(__DIR__));

// Vendor Constants
defined('FRONTENDER_VENDORS') OR define('FRONTENDER_VENDORS', FRONTENDER_PATH . "/vendors");
	defined('YCOMP_VER') OR define('YCOMP_VER', '2.4.7');
	defined('YUI_COMPRESSOR') OR define('YUI_COMPRESSOR', FRONTENDER_VENDORS . "/yuicompressor-" . YCOMP_VER . ".jar");

// Library Constants
defined('FRONTENDER_LIBS') OR define('FRONTENDER_LIBS', FRONTENDER_PATH . "/libraries");
defined('FRONTENDER_SRC') OR define('FRONTENDER_SRC', FRONTENDER_LIBS . "/assetic/src/Assetic");

/**
 * Load in project dependancies which include 
 * LessPHP, Assetic and Symfony Process component
 */
require __DIR__ . '/libraries.php';

Dispatcher::applyFilter('run', function($self, $params, $chain) {

	// filter over css files
	if(strstr($params['request']->url, '.css')) {
		
		header('Content-Type: text/css');

		$key = preg_replace("/^(css\/)/", 'templates/', $params['request']->url);

		return Cache::read('default', $key);

	}

	$result = $chain->next($self, $params, $chain);

	return $result;


});