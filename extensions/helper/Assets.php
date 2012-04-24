<?php

namespace li3_frontender\extensions\helper;

use \lithium\core\Environment;
use \lithium\core\Libraries;
use \lithium\storage\Cache;
use \lithium\util\String;

// Assetic Classes
use Assetic\AssetManager;
use Assetic\Asset\AssetCollection;
use Assetic\Asset\FileAsset;
use Assetic\Asset\GlobAsset;

// Assetic Filters
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
			'assets_root' => LITHIUM_APP_PATH . "/webroot"
		);

		$this->_config += $defaults;

		// remove extra slash if it was included in the library config
		$this->_config['assets_root'] = (substr($this->_config['assets_root'], -1) == "/") 
			? substr($this->_config['assets_root'], 0, -1) : $this->_config['assets_root'];

		$this->_paths['styles'] =  $this->_config['assets_root'] . "/css/";
		$this->_paths['scripts'] = $this->_config['assets_root'] . "/js/";

		if($this->_config['compress']){
			$this->styles->ensureFilter( new Yui\CssCompressorFilter( YUI_COMPRESSOR ) );
			$this->scripts->ensureFilter( new Yui\JsCompressorFilter( YUI_COMPRESSOR ) );
		}

	}

	public function style($stylesheets) {

		$defaults = array('type' => 'stylesheet', 'inline' => true);

		$filename = "";

		$stats = array('modified' => 0, 'size' => 0);

		foreach($stylesheets as $sheet){

			$path = $this->_paths['styles'] . $sheet . ".css";

			$filters = array();

			// its a less file
			if(preg_match("/(.less)$/is", $sheet)){

				$path = $this->_paths['styles'] . $sheet;

				$filters = array( new LessphpFilter() );

			}

			$filename .= $path;

			$_stat = stat($path);

			$stats['modified'] += $_stat['mtime'];
			$stats['size'] += $_stat['size'];

			$this->styles->add( 
				new FileAsset( $path , $filters )
			);

		} 

		// Hashed filename without stats appended.
		$_rawFilename = String::hash($filename, array('type' => 'sha1'));

		$filename = "{$_rawFilename}_{$stats['size']}_{$stats['modified']}.css";

		// If Cache doesn't exist then we recache
		// Recache removes old caches and adds the new
		// ---
		// If you change a file in the styles added then a recache is made due
		// to the fact that the file stats changed
		if(!$cached = Cache::read('default', "templates/{$filename}")){

			$this->recache($filename, $this->styles->dump());			

		}

		echo $this->_context->helper('html')->style("{$filename}");

	}

	private function recache($filename, $content, $options = array()){

		$defaults = array(
			'length' => '+1 year'
		);

		$options += $defaults;

		$name_sections = explode('_', $filename);

		$like_files = $name_sections[0];

		// loop thru cache and delete old cache file
		if ($handle = opendir(CACHE_DIR)) {

			while (false !== ($oldfile = readdir($handle))) {
			
				if(preg_match("/^{$like_files}/", $oldfile)){

					Cache::delete('default', "templates/{$oldfile}");

				}

			}

			closedir($handle);

		}

		Cache::write('default', "templates/{$filename}", $this->styles->dump(), $options['length']);

	}

}