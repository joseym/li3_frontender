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

defined('CACHE_DIR') OR define('CACHE_DIR', Libraries::get(true, 'resources') . "/tmp/cache/templates");

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

		// Return cached stylesheet
		if($cache = Cache::read('default', $key)){
			return $cache;
		// throw 404
		} else {
			header('Content-Type: text/css', true, 404);
			return;
		}

	}

	$result = $chain->next($self, $params, $chain);

	return $result;


});