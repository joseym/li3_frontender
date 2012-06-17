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
$options =  Libraries::get('li3_frontender') + array('source' => 'composer', 'cacheOnly' => false);

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

Dispatcher::applyFilter('run', function($self, $params, $chain) use ($options) {

	$assets = array(
			'css' => array(
					'match' => "/.css$/",
					'type' => 'text/css',
					'name' => 'Stylesheet',
			),
			'js' => array(
					'match' => "/.js$/",
					'type' => 'text/javascript',
					'name' => 'Script'
			),
	);
	foreach(array_keys($assets) as $type) {
		$config = $assets[$type];
		$config['cacheOnly'] = $options['cacheOnly'];
		if(preg_match($config['match'], $params['request']->url)) {
			if (readCache($params['request'], $config)) {
				return;
			}
		}

	}

	return $chain->next($self, $params, $chain);

});

/**
 * Read cache files for assets
 * Returns CSS/Js from Cache if exists
 * @param  object $request
 * @return string          contents of cache file
 */
function readCache($request, array $options) {


	$http_response_code = null;
	if (!$content = Cache::read('default', $request->url)) {
		// TODO: config switch
		if (!$options['cacheOnly']) {
			return false;
		}
		$http_response_code = 404;
		$content = '404: ' . $options['name'] . ' does not exist';
	}

	// TODO: use lithium constructs
	header('Content-Type: '. $config['type'], true, $http_response_code);
	echo $content;
	return true;
};
