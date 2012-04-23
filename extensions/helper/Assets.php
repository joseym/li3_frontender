<?php

namespace li3_frontender\extensions\helper;

use \lithium\core\Libraries;
use \lithium\net\http\Media;
use \lithium\storage\Cache;
use \lithium\core\Object;

// Assetic Classes
use Assetic\Asset\AssetCollection;
use Assetic\AssetManager;
use Assetic\Asset\FileAsset;
use Assetic\Asset\GlobAsset;
use Assetic\Filter\LessphpFilter;
use Assetic\Filter\Yui;
use Assetic\Cache\FilesystemCache;

class Assets extends \lithium\template\Helper {

	protected $_config;
	protected $styles;
	protected $scripts;
	protected $assetsPath;
	protected $_paths;

	public function _init(){

		parent::_init();

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

		$this->_paths['styles'] = $this->_config['assets_root'] . "/css/";
		$this->_paths['scripts'] = $this->_config['assets_root'] . "/js/";

		if($this->_config['compress']){
			$this->styles->ensureFilter( new Yui\CssCompressorFilter( YUI_COMPRESSOR ) );
			$this->scripts->ensureFilter( new Yui\JsCompressorFilter( YUI_COMPRESSOR ) );
		}

	}

	public function style($stylesheets) {

		$defaults = array('type' => 'stylesheet', 'inline' => true);

		foreach($stylesheets as $sheet){

			// its a less file
			if(preg_match("/(.less)$/is", $sheet)){
				$this->styles->add( new FileAsset( $this->_paths['styles'] . $sheet, array( new LessphpFilter() ) ) );
				continue;
			}

			$this->styles->add( new FileAsset( $this->_paths['styles'] . $sheet . ".css" ) );

		} 

		// this will echo CSS compiled by LESS and compressed by YUI
		echo $this->styles->dump();
		die();

	}

	private function setCollection(array $options = array(), $type){
		// if($type !== 'styles' OR $type !== 'scripts') return;

		$this->styles = new AssetCollection(array($options));

	}

	public function getCollection($type){

		return $this->styles;

	}

}