<?php

namespace li3_frontender\extensions\helper;

use RuntimeException;
use \lithium\core\Environment;
use \lithium\core\Libraries;
use \lithium\storage\Cache;
use \lithium\util\String;

// Assetic Classes
use Assetic\Asset\AssetCollection;
use Assetic\Asset\FileAsset;
use Assetic\Asset\GlobAsset;

// Assetic Filters
use Assetic\Filter\CoffeeScriptFilter;
use Assetic\Filter\LessphpFilter;
use Assetic\Filter\Yui;

class Assets extends \lithium\template\Helper {

	protected $_config;
	protected $styles;
	protected $scripts;
	protected $assetsPath;
	protected $_paths;

	public function _init(){

		parent::_init();
		// print_r(Cache::config());
		// print_r(Environment::get() == 'development');

		$this->styles =  new AssetCollection();
		$this->scripts = new AssetCollection();

		$this->_config = Libraries::get('li3_frontender');

		$defaults = array(
			'compress' => false,
			'assets_root' => LITHIUM_APP_PATH . "/webroot",
			'production' => (Environment::get() == 'production')
		);

		$this->_config += $defaults;

		// remove extra slash if it was included in the library config
		$this->_config['assets_root'] = (substr($this->_config['assets_root'], -1) == "/") 
			? substr($this->_config['assets_root'], 0, -1) : $this->_config['assets_root'];

		$this->_paths['styles'] =  $this->_config['assets_root'] . "/css/";
		$this->_paths['scripts'] = $this->_config['assets_root'] . "/js/";

		if($this->_config['compress'] OR 
			$this->_config['production']){
			$this->styles->ensureFilter( new Yui\CssCompressorFilter( YUI_COMPRESSOR ) );
			$this->scripts->ensureFilter( new Yui\JsCompressorFilter( YUI_COMPRESSOR ) );
		}

	}

	/**
	 * Takes styles and parses them with appropriate filters and, if in 
	 * production, merges them all into a single file.
	 * @param  array $stylesheets list of stylesheets
	 * @return string              stylesheet `<link>` tag
	 */
	public function style($stylesheets) {

		$defaults = array('type' => 'stylesheet', 'inline' => true);

		$filename = ""; // will store concatenated filename

		$stats = array('modified' => 0, 'size' => 0); // stores merged file stats

		// loop over the sheets that were passed and run them thru Assetic
		foreach($stylesheets as $sheet){

			$path = $this->_paths['styles'] . $sheet . ".css";

			$filters = array();

			// its a less file
			if(preg_match("/(.less)$/is", $sheet)){

				$path = $this->_paths['styles'] . $sheet;

				$filters = array( new LessphpFilter() );

			}

			$filename .= $path;

			if(file_exists($path)){

				$_stat = stat($path);

				$stats['modified'] += $_stat['mtime'];
				$stats['size'] += $_stat['size'];
				$stats[$path]['modified'] = $_stat['mtime'];
				$stats[$path]['size'] = $_stat['size'];

				$this->styles->add( 
					new FileAsset( $path , $filters )
				);

			} else {

				throw new RuntimeException("The stylesheet '{$path}' does not exist");

			}

		} 

		// If in production merge files and server up a single stylesheet
		if($this->_config['production']){

			// Hashed filename without stats appended.
			$_rawFilename = String::hash($filename, array('type' => 'sha1'));
			echo $this->buildStyleLink($_rawFilename, $this->styles, $stats);

		} else {

			foreach($this->styles as $leaf){

				$filename = "{$leaf->getSourceRoot()}/{$leaf->getSourcePath()}";
				$_rawFilename = String::hash($filename, array('type' => 'sha1'));
				// die($filename);
				// $_rawFilename = preg_replace("/(.css|.less)$/is", replacement, subject);
				$stat = $stats[$filename];

				echo $this->buildStyleLink($_rawFilename, $leaf, $stat);

			}

		}

	}

	public function script($scripts) {

		foreach($scripts as $script){

			$path = $this->_paths['scripts'] . $script . ".js";

			$filters = array();

			// its a less file
			if(preg_match("/(.coffee)$/is", $script)){

				$path = $this->_paths['scripts'] . $script;

				$filters = array( new CoffeeScriptFilter() );

			}

			$this->scripts->add( 
				new FileAsset( $path , $filters )
			);

		} 

		// If in production merge files and server up a single stylesheet
		foreach($this->scripts as $leaf){

			$filename = "{$leaf->getSourceRoot()}/{$leaf->getSourcePath()}";
			// $_rawFilename = String::hash($filename, array('type' => 'sha1'));
			echo $leaf->dump();
			die();
			// $_rawFilename = preg_replace("/(.css|.less)$/is", replacement, subject);

			echo $this->buildStyleLink($_rawFilename, $leaf, array('modified' => 10, 'size' => 10));

		}


	}

	/**
	 * Check cache and spits out the style link
	 * @param  string $filename name of the cache file
	 * @param  object $content  Assetic style object
	 * @param  array $stats    file stats
	 * @return string           lithium link helper
	 */
	private function buildStyleLink($filename, $content, $stats){

		$filename = "{$filename}_{$stats['size']}_{$stats['modified']}.css";

		// If Cache doesn't exist then we recache
		// Recache removes old caches and adds the new
		// ---
		// If you change a file in the styles added then a recache is made due
		// to the fact that the file stats changed
		if(!$cached = Cache::read('default', "css/{$filename}")){
			$this->setCache($filename, $content->dump(), array('location' => 'css'));			
		}

		// pass single stylesheet link
		return $this->_context->helper('html')->style("{$filename}");

	}

	/**
	 * Rebuild asset cache file
	 * @param  string $filename The name of the file to cache or test caching on
	 * @param  string $content  File contents to be cached
	 * @param  array  $options  Options to handle cache length, location, so on.
	 */
	private function setCache($filename, $content, $options = array()){

		$defaults = array(
			'length' => '+1 year',
			'location' => 'templates'
		);

		$options += $defaults;

		$name_sections = explode('_', $filename);

		$like_files = $name_sections[0];

		if (!is_dir($cache_location = CACHE_DIR . "/{$options['location']}")) {
			mkdir($cache_location, 0755, true);
		}

		// loop thru cache and delete old cache file
		if ($handle = opendir($cache_location)) {

			while (false !== ($oldfile = readdir($handle))) {
			
				if(preg_match("/^{$like_files}/", $oldfile)){

					Cache::delete('default', "{$options['location']}/{$oldfile}");

				}

			}

			closedir($handle);

		}

		Cache::write('default', "{$options['location']}/{$filename}", $content, $options['length']);

	}

}