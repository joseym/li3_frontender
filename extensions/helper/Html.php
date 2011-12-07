<?php

namespace assets\extensions\helper;
use \lithium\core\Libraries;
use \lithium\net\http\Media;
use \lithium\storage\Cache;

if (!defined('DS')) define('DS', DIRECTORY_SEPARATOR);

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
	 * Creates a link element for CSS/LessCSS stylesheets.
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
	
	/**
	 * Pass Less Stylesheet thru the LessPHP Parser
	 *
	 * @param string $input Path to file that is to be converted
	 */
	private function _parseLess($input){
	
		$this->webroot = Media::webroot(true);
		
		$css_root = $this->webroot . DS . 'css' . DS;
		
		$less_file = $css_root . $input;
		
		$output_path = LITHIUM_APP_PATH . DS . 'resources' . DS . 'tmp' . DS . 'cache' . DS . 'templates' . DS;
		
		$output = $output_path . $input . '.css';
		
		// Set caching times - by default cache is distant past
		$time = mktime(0,0,0,21,5,1980);
	
		// check files modification time
		$modTime = filemtime($less_file);
		
		// Set time reference to the files modification time if file is newer
		if($modTime > $time) {
			$time = $modTime;
		}
		
		// Find Cached File
		if(file_exists($output)) {
			// get file mod time
			$cacheTime = filemtime($output);
			
			// If cached is older then css file recompile
			if($cacheTime < $time) { 
				$time = $cacheTime;
				$recache = true;
			} else {
				$recache = false;
			}
			
		} else {
			$recache = true;
		}
		
		if($recache) {
		
			include(LITHIUM_APP_PATH . DS . 'libraries' . DS . 'assets' . DS . 'lessphp' . DIRECTORY_SEPARATOR . 'lessc.inc.php');
			
			$less = new \lessc();
			
			$css .= file_get_contents($less_file);
			
			$css = $less->parse($css);
			
			// Test the date the css was compiled
			$css = "/* Compile Date: ". date('m/d/Y H:i:s') ." */\r\n\r\n" . $css;
	
			file_put_contents($output, $css);

		} else {
			
			$css = file_get_contents($output);
			
		}
		
		return $css;
			
	}
    
}
?>