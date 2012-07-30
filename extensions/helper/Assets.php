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
	protected $_paths;
	protected $_production;
	protected $styles;
	protected $scripts;

	public function _init(){

		parent::_init();

		$this->styles =  new AssetCollection();
		$this->scripts = new AssetCollection();

		$this->_config = Libraries::get('li3_frontender');

		$defaults = array(
			'compress' => false,
			'assets_root' => LITHIUM_APP_PATH . "/webroot",
			'production' => (Environment::get() == 'production'),
			'locations' => array(
				'node' => '/usr/bin/node',
				'coffee' => '/usr/bin/coffee'
			)
		);

		$this->_config += $defaults;

		$this->_production = $this->_config['production'];

		// remove extra slash if it was included in the library config
		$this->_config['assets_root'] = (substr($this->_config['assets_root'], -1) == "/") 
			? substr($this->_config['assets_root'], 0, -1) : $this->_config['assets_root'];

		$this->_paths['styles'] =  $this->_config['assets_root'] . "/css/";
		$this->_paths['scripts'] = $this->_config['assets_root'] . "/js/";

		if($this->_config['compress'] OR $this->_production){
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

		$options = array(
			'type' => 'css',
			'filters' => array( new LessphpFilter() ),
			'path' => $this->_paths['styles']
		);

		$this->_runAssets($stylesheets, $options);

	}

	/**
	 * This is pretty much identical to the style method above
	 * I plan to consolidate these two.
	 */
	public function script($scripts) {

		$options = array(
			'type' => 'js',
			'filters' => array( new CoffeeScriptFilter($this->_config['locations']['coffee'], $this->_config['locations']['node']) ),
			'path' => $this->_paths['scripts']
		);

		$this->_runAssets($scripts, $options);

	}

	/**
	 * Method used to determine if an asset needs to be cached or timestamped.
	 * Makes appropriate calls based on this.
	 * @param  array  $files   [description]
	 * @param  array  $options [description]
	 * @return [type]          [description]
	 */
	private function _runAssets(array $files = array(), array $options = array()) {

		$filename = ""; // will store concatenated filename

		$stats = array('modified' => 0, 'size' => 0); // stores merged file stats

		// request type
		$type = ($options['type'] == 'css') ? 'styles' : 'scripts';

		// loop over the sheets that were passed and run them thru Assetic
		foreach($files as $file){
			
			$_filename = $file;
			$path = $options['path'];
			
			// build filename if not a less file
			if(($isSpecial = $this->specialExt($file)) 
				OR (preg_match("/(.css|.js)$/is", $file))){
				$path .= $file;
			} else {
				$path .= "{$file}.{$options['type']}";
				$_filename = "{$file}.{$options['type']}";
			}

			// ensure file exists, if so set stats
			if(file_exists($path)){

				$_stat = stat($path);

				$stats['modified'] += $_stat['mtime'];
				$stats['size'] += $_stat['size'];
				$stats[$path]['modified'] = $_stat['mtime'];
				$stats[$path]['size'] = $_stat['size'];

			} else {

				throw new RuntimeException("The {$options['type']} file '{$path}' does not exist");

			}

			$filters = array();

			// its a less or coffee file
			if($isSpecial){

				$path = $options['path'] . $file;

				$filters +=  $options['filters'];

			} else {

				// If we're not in production and we're not compressingthen we 
				// dont need to cache static css assets
				if(!$this->_production AND !$this->_config['compress']){

					$method = substr($type, 0, -1);
					echo $this->_context->helper('html')->{$method}("{$_filename}?{$stats[$path]['modified']}") . "\n\t";
					continue;

				}

			}

			$filename .= $path;

			// add asset to assetic collection
			$this->{$type}->add( 
				new FileAsset( $path , $filters )
			);

		} 

		// If in production merge files and server up a single stylesheet
		if($this->_production){

			// Hashed filename without stats appended.
			$_rawFilename = String::hash($filename, array('type' => 'sha1'));
			echo $this->buildHelper($_rawFilename, $this->{$type}, array('type' => $options['type'], 'stats' => $stats));

		} else {

			// not production so lets serve up individual files (better debugging)
			foreach($this->{$type} as $leaf){

				$filename = "{$leaf->getSourceRoot()}/{$leaf->getSourcePath()}";
				$_rawFilename = String::hash($filename, array('type' => 'sha1'));
				$stat = isset($stats[$filename]) ? $stats[$filename] : false;

				if ($stat) echo $this->buildHelper($_rawFilename, $leaf, array('type' => $options['type'], 'stats' => $stat));

			}

		}

	}

	/**
	 * Check cache and spits out the style/script link
	 * @param  string $filename name of the cache file
	 * @param  object $content  Assetic style object
	 * @param  array $options    file stats and helper type
	 * @return string           lithium link helper
	 */
	private function buildHelper($filename, $content, array $options = array()){

		// print_r($options);

		$filename = "{$filename}_{$options['stats']['size']}_{$options['stats']['modified']}.{$options['type']}";

		// If Cache doesn't exist then we recache
		// Recache removes old caches and adds the new
		// ---
		// If you change a file in the styles added then a recache is made due
		// to the fact that the file stats changed
		if(!$cached = Cache::read('default', "{$options['type']}/{$filename}")){
			$this->setCache($filename, $content->dump(), array('location' => $options['type']));			
		}

		// pass single stylesheet link
		switch($options['type']){
			case 'css':
				return $this->_context->helper('html')->style("{$filename}") . "\n\t";
			case 'js':
				return $this->_context->helper('html')->script("{$filename}") . "\n\t";
		}

	}

	/**
	 * Rebuild asset cache file
	 * @param  string $filename The name of the file to cache or test caching on
	 * @param  string $content  File contents to be cached
	 * @param  array  $options  Options to handle cache length, location, so on.
	 */
	private function setCache($filename, $content, $options = array()){

		// Create css cache dir if it doesnt exist.
		if (!is_dir($cache_location = CACHE_DIR . "/{$options['location']}")) {
			mkdir($cache_location, 0755, true);
		}

		$defaults = array(
			'length' => '+1 year',
			'location' => 'templates'
		);

		$options += $defaults;

		$name_sections = explode('_', $filename);

		$like_files = $name_sections[0];


		// loop thru cache and delete old cache file
		if (!$this->_production && $handle = opendir($cache_location)) {

			while (false !== ($oldfile = readdir($handle))) {
			
				if(preg_match("/^{$like_files}/", $oldfile)){

					Cache::delete('default', "{$options['location']}/{$oldfile}");

				}

			}

			closedir($handle);

		}

		Cache::write('default', "{$options['location']}/{$filename}", $content, $options['length']);

	}

	private function specialExt($filename){

		if(preg_match("/(.less|.coffee)$/is", $filename, $matches)){
			$ext = $matches[0];
		} else {
			$ext = false;
		}

		return $ext;
	}

}