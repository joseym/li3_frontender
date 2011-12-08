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
	 * String templates used by this helper.
	 * Mimes the templates from parent
	 * 
	 * # all are required for now because errors arise if I attempt to be selective #
	 *
	 * @var array
	 */
	protected $_strings = array(
		'block'            => '<div{:options}>{:content}</div>',
		'block-end'        => '</div>',
		'block-start'      => '<div{:options}>',
		'charset'          => '<meta charset="{:encoding}" />',
		'image'            => '<img src="{:path}"{:options} />',
		'js-block'         => '<script type="text/javascript"{:options}>{:content}</script>',
		'js-end'           => '</script>',
		'js-start'         => '<script type="text/javascript"{:options}>',
		'link'             => '<a href="{:url}"{:options}>{:title}</a>',
		'list'             => '<ul{:options}>{:content}</ul>',
		'list-item'        => '<li{:options}>{:content}</li>',
		'meta'             => '<meta{:options}/>',
		'meta-link'        => '<link href="{:url}"{:options} />',
		'para'             => '<p{:options}>{:content}</p>',
		'para-start'       => '<p{:options}>',
		'script'           => '<script type="text/javascript" src="{:path}"{:options}></script>',
		'style'            => '<style type="text/css"{:options}>{:content}</style>',
		'style-import'     => '<style type="text/css"{:options}>@import url({:url});</style>',
		'style-link'       => '<link rel="{:type}" type="text/css" href="{:path}?{:mtime}"{:options} />',
		'table-header'     => '<th{:options}>{:content}</th>',
		'table-header-row' => '<tr{:options}>{:content}</tr>',
		'table-cell'       => '<td{:options}>{:content}</td>',
		'table-row'        => '<tr{:options}>{:content}</tr>',
		'tag'              => '<{:name}{:options}>{:content}</{:name}>',
		'tag-end'          => '</{:name}>',
		'tag-start'        => '<{:name}{:options}>'
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
		
		$defaults = array('type' => 'stylesheet', 'inline' => true);
		
		list($scope, $options) = $this->_options($defaults, $options);

		if (is_array($path)) {
			foreach ($path as $i => $item) {
				$path[$i] = $this->style($item, $scope);
			}
			return ($scope['inline']) ? join("\n\t", $path) . "\n" : null;
		}
		
		$method = __METHOD__;
		$type = $scope['type'];
		
		$file_extension = file_exists($this->webroot . DS . 'css' . DS . $path . '.less') ? '.less' : '.css';
		
		$stats = stat($this->webroot . DS . 'css' . DS . $path . $file_extension);
		
		$mtime = $stats['mtime'];
		
		$params = compact('type', 'path', 'options', 'mtime');

		$filter = function($self, $params, $chain) use ($defaults, $method) {
			$template = ($params['type'] == 'import') ? 'style-import' : 'style-link';
			return $self->invokeMethod('_render', array($method, $template, $params));
		};
		
		$style = $this->_filter($method, $params, $filter);

		if ($scope['inline']) {
			return $style;
		}
		if ($this->_context) {
			$this->_context->styles($style);
		}
	}
	    
}
?>