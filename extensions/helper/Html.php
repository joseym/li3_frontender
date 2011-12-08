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
		
		$parent = parent::style($path, $options);
		return $parent;
		
	}
	    
}
?>