<?php
/**
 *
 */
use lithium\core\Libraries;
use lithium\storage\Cache;
use lithium\net\http\Media;

defined('FRONTENDER_PATH') OR define('FRONTENDER_PATH', dirname(__DIR__));
defined('FRONTENDER_LIBS') OR define('FRONTENDER_LIBS', FRONTENDER_PATH . "/libraries");
defined('FRONTENDER_SRC') OR define('FRONTENDER_SRC', FRONTENDER_LIBS . "/assetic/src/Assetic");

/**
 * Pull in asset libraries
 */
Libraries::add("lessc", array(
	// "prefix" => "Solarium_",
	"path" => FRONTENDER_LIBS . "/lessphp",
	"bootstrap" => "lessc.inc.php",
	// "loader" => array("Solarium_Autoloader", "load")
));
Libraries::add("Assetic", array(
	// "prefix" => "Solarium_",
	"path" => FRONTENDER_SRC,
	"bootstrap" => false,
	// "loader" => array("Solarium_Autoloader", "load")
));
