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
	 * Path to media/webroot directory
	 * 
	 * @var string
	 */
	public $webroot;

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
		
		// Loop thru paths passed to the helper
		if(is_array($path)){
			foreach((array)$path as $index => $sheet){
			
				// see if its less or css
				$ext = file_exists(Media::path("css/{$sheet}.less", "cs")) ? 'less' : 'css';
				// add the files timestamp
				$add_timestamp = Media::asset("css/{$sheet}.{$ext}", "cs", array('timestamp' => true));
				
				if($ext == 'less'){
					// cast the less file as a css file, the filter will determine if its less
					$add_timestamp = preg_replace(array("/\.less/"), array(".css"), $add_timestamp);
				}
				
				// store the modified path
				$path[$index] = $add_timestamp;
			}
		}
		
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
		
		return parent::image($path, $options);
		
	}

	    
}
?>