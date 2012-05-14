<?php
/**
 *
 */
use lithium\core\Libraries;
use lithium\storage\Cache;
use lithium\action\Dispatcher;

/**
 * get library options, assign defaults
 */
$options =  Libraries::get('li3_frontender') + array('source' => 'composer');

// Plugin Location
defined('FRONTENDER_PATH') OR define('FRONTENDER_PATH', dirname(__DIR__));

// Vendor Constants
defined('FRONTENDER_VENDORS') OR define('FRONTENDER_VENDORS', FRONTENDER_PATH . "/vendors");
	defined('YCOMP_VER') OR define('YCOMP_VER', '2.4.7');
	defined('YUI_COMPRESSOR') OR define('YUI_COMPRESSOR', FRONTENDER_VENDORS . "/yuicompressor-" . YCOMP_VER . ".jar");

// Library Constants
defined('FRONTENDER_LIBS') OR define('FRONTENDER_LIBS', FRONTENDER_PATH . "/libraries");
defined('FRONTENDER_SRC') OR define('FRONTENDER_SRC', FRONTENDER_LIBS . "/assetic/src/Assetic");

defined('CACHE_DIR') OR define('CACHE_DIR', Libraries::get(true, 'resources') . "/tmp/cache");

/**
 * Load in project dependancies which include 
 * LessPHP, Assetic and Symfony Process component
 */
require __DIR__ . '/libraries.php';

Dispatcher::applyFilter('run', function($self, $params, $chain) {

	// filter over css files
	if(strstr($params['request']->url, '.css')) { 
		return readCache($params['request'], 'text/css', 'Stylesheet does not exist'); 
	}

	if(strstr($params['request']->url, '.js')) { 
		return readCache($params['request'], 'text/javascript', 'Script does not exist'); 
	}

	$result = $chain->next($self, $params, $chain);

	return $result;


});

/**
 * Read cache files for assets
 * Returns CSS/Js from Cache if exists, otherwise returns a 404 message
 * @param  object $request
 * @return string          contents of cache file
 */
function readCache($request, $type, $errorMessage){

	header("Content-Type: {$type}");

	$key = $request->url;

	// Return cached stylesheet
	if($cache = Cache::read('default', $key)){
		echo $cache;

	// throw 404
	} else {
		header("Content-Type: {$type}", true, 404);
		$content = "404: {$errorMessage}";
		echo $content;
	}

};
