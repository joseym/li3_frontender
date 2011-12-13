<?php

namespace assets\extensions\helper;
use \lithium\core\Libraries;
use \lithium\net\http\Media;
use \lithium\storage\Cache;

/**
 * An extension of the core lithium html helper which, like the parent is accessible in the templates 
 * via `$this->html`, which will auto-load this helper into the rendering context. 
 */


class Html extends \lithium\template\helper\Html {

	/**
	 * Name of library
	 * In case I decide to rename it later
	 * assigned in bootstrap
	 * 
	 * @var string
	 */
	private $libary_name = PLUGIN_NAME;
	
	/**
	 * Path to media/webroot directory
	 * 
	 * @var string
	 */
	public $webroot;
	
	public $default_config = array(
		'css' => array(
			'cache_busting' => true,
			'minify' => false
		),
		'image' => array(
			'cache_busting' => true
		)
	);

	/**
	 * Mimes parent style function.
	 *
	 * @param mixed $path The name of a CSS/LessCSS style sheet in `/app/webroot/css`, or an array
	 *              containing names of CSS/LessCSS stylesheets in that directory.
	 * @param array $options Array of HTML attributes.
	 * @return string CSS <link /> or <style /> tag, depending on the type of link.
	 * @filter This method can be filtered.
	 */
	public function style($path, array $options = array()) {
		
		$this->webroot = Media::webroot(true);
		
		$library = Libraries::get($this->libary_name);
		
		// stage configuration array (if none is set in ::add then create a blank array)
		$library['config'] = (isset($library['config'])) ? $library['config'] : array();
		
		// set defaults for css config only if css isn't specified in ::add[config]
        if(!isset($library['config']['css'])){
			$library['config']['css'] = $this->default_config['css'];
        } 
		
		$bust = false;
		// If cache busting is enabled		
		if( $library['config']['css']['cache_busting'] or isset($options['cache_busting']) ){ 
			
			$bust = true;
			
			if(isset($options['cache_busting'])){
				$bust = ($options['cache_busting'] === false) ? false : true;
			}
			
		}
		
		if(is_array($path)){
			foreach((array)$path as $index => $sheet){
			
				// see if its less or css
				$ext = file_exists(Media::path("css/{$sheet}.less", "cs")) ? 'less' : 'css';
				
				// add the files timestamp
				if($bust){
					$sheet = Media::asset("css/{$sheet}.{$ext}", "cs", array('timestamp' => true));
				}
					
				// Minify
				if($ext == 'less'){
					// cast the less file as a css file, the filter will determine if its less
					$sheet = preg_replace(array("/\.less/"), array(".css"), $sheet);
				}
				
				if($library['config']['css']['minify']){
					$sheet = preg_replace(array("/\.css/"), array(".min.css"), $sheet);
				}
					
				// store the modified path
				$path[$index] = $sheet;
			}
		} else {
			
			$sheet = $path;
			
			// see if its less or css
			$ext = file_exists(Media::path("css/{$sheet}.less", "cs")) ? 'less' : 'css';
			
			// add the files timestamp
			if($bust){
				$sheet = Media::asset("css/{$sheet}.{$ext}", "cs", array('timestamp' => true));
			}
				
			// Minify
			if($ext == 'less'){
				// cast the less file as a css file, the filter will determine if its less
				$sheet = preg_replace(array("/\.less/"), array(".css"), $sheet);
			}
			
			if($library['config']['css']['minify']){
				$sheet = preg_replace(array("/\.css/"), array(".min.css"), $sheet);
			}
				
			// store the modified path
			$path = $sheet;
		}
				
		// We dont want to pass the "cache_busting" option to the renderer, its not a valid attribute
		if(isset($options['cache_busting'])) unset($options['cache_busting']);
		
		// Call the parent
		return parent::style($path, $options);
	}
	
	/**
	 * Mimes parent image function.
	 *
	 * @param string $path Path to the image file, relative to the app/webroot/img/ directory.
	 * @param array $options Array of HTML attributes.
	 * @return string
	 * @filter This method can be filtered.
	 */
 	public function image($path, array $options = array()) {
 	
		$library = Libraries::get($this->libary_name);
		$library['config'] = (isset($library['config'])) ? $library['config'] : array();
		
		// set defaults for css config only if css isn't specified in ::add[config]
        if(!isset($library['config']['image'])){
			$library['config']['image'] = $this->default_config['image'];
        } 
		
		// If cache busting is enabled		
		if( $library['config']['image']['cache_busting'] or isset($options['cache_busting']) ){ 
			
			$bust = true;
			
			if(isset($options['cache_busting'])){
				$bust = ($options['cache_busting'] === false) ? false : true;
			}
			
			if($bust){
			
		 		$is_local = true;
		 		
				$parsed_path = parse_url($path);
				
				if(isset($parsed_path['host'])){
					$is_local = ($parsed_path['host'] !== $_SERVER['SERVER_NAME']) ? false : true;
				}
				
				if($is_local){
				
					$image_path = $parsed_path['path'];
					if(!preg_match("/^\/img\//", $image_path)){
						$image_path = "/img/" . $image_path;
					}
					
					$path = Media::asset($image_path, "img", array('timestamp' => true));
					
				}
			
			}
			
		}
		
		// We dont want to pass the "cache_busting" option to the renderer, its not a valid attribute
		if(isset($options['cache_busting'])) unset($options['cache_busting']);
		
		return parent::image($path, $options);
		
	}

	    
}
?>