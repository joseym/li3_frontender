<?php
/**
 *
 */
use lithium\action\Dispatcher;
use lithium\core\Libraries;
use lithium\storage\Cache;

date_default_timezone_set('America/Chicago');

if (!defined('DS')) define('DS', DIRECTORY_SEPARATOR);

require LITHIUM_APP_PATH . DS . 'libraries' . DS . 'assets' . DS . 'lessphp' . DIRECTORY_SEPARATOR . 'lessc.inc.php';

/**
 * Intercepts a link element for CSS/LessCSS stylesheets.
 *
 * If the stylesheet is a lessCSS stylesheet it renders or generates it from cache.
 * 
 */
Dispatcher::applyFilter('run', function($self, $params, $chain) {
	
	// check to see if its a css file
	if(strstr($params['request']->url, '.css')) {
		
		// the HTML style helper assumes CSS file extension.
		// change to .less and see if the less version exists (below)
		$less_file = str_ireplace('.css', '.less', $params['request']->url);
		
 		$stats = stat($less_file);
		$dir = dirname($less_file);
		
		// Prep file for cache naming
		$oname = preg_replace(array("/^css|\.less/", "/\//"), array("", "_"), $less_file);
		
		// Build cache filename
		$cache_name = "style{$oname}_{$stats['ino']}_{$stats['mtime']}_{$stats['size']}.css";
		
		// Cached CSS file
		$css_file = Libraries::get(true, 'resources') . DS . 'tmp' . DS . 'cache' . DS . 'templates' . DS . $cache_name;
		
		// LessCSSify things
		if(file_exists($less_file)) {
			
			header('Content-Type: text/css');

			// if there's an up-to-date css file, serve it
			if(file_exists($css_file) && filemtime($css_file) >= filemtime($less_file)) {
				return file_get_contents($css_file);
			}
			
			// Try to generate new cache file
			try {
				$less = new lessc($less_file);
				$output = array(
					'/**',
					' * generated '.date('r'),
					' * ',
					' */'
				);
				$output = join(PHP_EOL, $output) . PHP_EOL . $less->parse();
			} catch(Exception $e) {
				header('Content-Type: text/css', true, 500);
				$output = "/* less compiler exception: {$e->getMessage()} */";
			}

			// Store cache file and serve the output to the browser
			file_put_contents($css_file, $output);
			return $output;

		}

	}
		
	return $chain->next($self, $params, $chain);

});

?>
