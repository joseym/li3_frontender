<?php
/**
 *
 */
use lithium\action\Dispatcher;
use lithium\core\Libraries;
use lithium\storage\Cache;
use lithium\net\http\Media;

date_default_timezone_set('America/Chicago');

if (!defined('DS')) define('DS', DIRECTORY_SEPARATOR);

/**
 * Name of library
 * In case I decide to rename it later
 * 
 * @constant string
 */
if(!defined('PLUGIN_NAME')) define('PLUGIN_NAME', 'assets');
if(!defined('CACHE_DIR')) define('CACHE_DIR', Libraries::get(true, 'resources') . DS . 'tmp' . DS . 'cache' . DS . 'templates');
if(!defined('LIB_PATH')) define('LIB_PATH', dirname(dirname(__DIR__)) . DS);

require LITHIUM_APP_PATH . DS . 'libraries' . DS . PLUGIN_NAME . DS . 'lessphp' . DS . 'lessc.inc.php';
require LITHIUM_APP_PATH . DS . 'libraries' . DS . PLUGIN_NAME . DS . 'css_minify.php';

/**
 * Intercepts a link element for CSS/LessCSS stylesheets.
 *
 * If the stylesheet is a lessCSS stylesheet it renders or generates it from cache.
 * 
 */
Dispatcher::applyFilter('run', function($self, $params, $chain) {

	// check to see if its a css file
	if(strstr($params['request']->url, '.css')) {
				
		header('Content-Type: text/css');

		$path_array = explode('/', $params['request']->url);
		
		if($path_array[0] == 'css'){
			array_splice($path_array, 0, 0, array("webroot"));
			$path =  LITHIUM_APP_PATH . DS . implode($path_array, '/');
		} else {
			array_splice($path_array, 1, 0, array("webroot"));
			$path =  LIB_PATH . implode($path_array, '/');
		}
				
		$is_min = preg_match("/\.min/", $params['request']->url);
		
		$library = Libraries::get(PLUGIN_NAME);
		
		// stage configuration array (if none is set in ::add then create a blank array)
		$library['config'] = (isset($library['config'])) ? $library['config'] : array();
		
		// set defaults for css config only if css isn't specified in ::add[config]
        if(!isset($library['config']['css'])){
			$library['config']['css'] = array(
	            'cache_busting' => true,
	            'minify' => true
	        );
        } 
				
		// the HTML style helper assumes CSS file extension.
		// change to .less and see if the less version exists (below)
		$less_file = preg_replace("/\.css|\.min.css/", ".less", $path);
		
		$file = file_exists($less_file) ? $less_file : preg_replace('/\.min/', '', $path);		
				
		if(!file_exists($file)){
			header('Content-Type: text/css', true, 404);
			return;
		}
		
 		$stats = stat($file);
		
		// Prep file for cache naming
		$oname = preg_replace(array("/^css|\.less|\.css/", "/\/|\./"), array("", "_"), $params['request']->url);
		
		// Build cache filename
		$cache_name = "style{$oname}_{$stats['ino']}_{$stats['mtime']}_{$stats['size']}.css";
		
		// Cached CSS file
		$css_file = CACHE_DIR . DS . $cache_name;

		// LessCSSify things
		if(file_exists($less_file)) {
			
			$output = parseLess($less_file, $css_file);
			
		}
				
		if($is_min){
		
			// if there's an up-to-date css file, serve it
			if(file_exists($css_file) && filemtime($css_file) >= filemtime($file)) {
				return file_get_contents($css_file);
			} else {
				$contents = (file_exists($less_file)) ? $output : file_get_contents($file);
				$output = minify($contents, $css_file);
			}
		
								
		}
		
		return storeCache($output, $css_file);
		
	}
	
	$result = $chain->next($self, $params, $chain);
	
	return $result;

});

/**
 * Takes the contents of a parsed file and stores it in file cache.
 *
 * @param string $contents string of parsed file contents.
 * @param string $file the path to where `$contents` is to be stored
 *			this can be anything but best practice is to store in
 *			`resources/tmp/cache/templates`
 */
function storeCache($contents, $file){
		
	$filename = basename($file);
	
	// get the parsed path, removing the timestamp and size
	$filename =  implode("_", array_splice(explode("_", $filename), 0, 2));
	
	// loop thru files in cache and delete old cache file
	if ($handle = opendir(CACHE_DIR)) {

	    while (false !== ($oldfile = readdir($handle))) {
	    	if(preg_match("/{$filename}/", $oldfile)){
				
		        unlink(CACHE_DIR . DS . $oldfile);
		        				        
	    	}
	    }
				
	    closedir($handle);
	}
	
	// Store cache file and serve the output to the browser
	file_put_contents($file, $contents);
	
	return $contents;
			
}

function parseLess($less_file, $output_file){
	
	$library = Libraries::get(PLUGIN_NAME);
	
	if($library['config']['environment'] == strtolower('production')){
		// if there's an up-to-date css file, serve it
		if(file_exists($output_file) && filemtime($output_file) >= filemtime($less_file)) {
			return file_get_contents($output_file);
		}		
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
	
	// store as a cached file and return the parsed contents
	return $output;
	
}

function minify($content, $output_file){
	
	$output = Minify_CSS_Compressor::process($content, array(
        'removeCharsets' => true,
        'preserveComments' => false,
        'currentDir' => null,
        'docRoot' => $_SERVER['DOCUMENT_ROOT'],
        'prependRelativePath' => null,
        'symlinks' => array(),
    ));

	return $output;
	
}