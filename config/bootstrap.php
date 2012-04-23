<?php
/**
 *
 */
use lithium\core\Libraries;
use lithium\storage\Cache;
use lithium\net\http\Media;

// Plugin Location
defined('FRONTENDER_PATH') OR define('FRONTENDER_PATH', dirname(__DIR__));

// Vendor Constants
defined('FRONTENDER_VENDORS') OR define('FRONTENDER_VENDORS', FRONTENDER_PATH . "/vendors");
	defined('YCOMP_VER') OR define('YCOMP_VER', '2.4.7');
	defined('YUI_COMPRESSOR') OR define('YUI_COMPRESSOR', FRONTENDER_VENDORS . "/yuicompressor-" . YCOMP_VER . ".jar");

// Library Constants
defined('FRONTENDER_LIBS') OR define('FRONTENDER_LIBS', FRONTENDER_PATH . "/libraries");
defined('FRONTENDER_SRC') OR define('FRONTENDER_SRC', FRONTENDER_LIBS . "/assetic/src/Assetic");

/**
 * Pull in asset libraries
 */
Libraries::add("Symfony", array(
	"path" => FRONTENDER_LIBS . "/symfony",
	"bootstrap" => false,
));

Libraries::add("lessc", array(
	"path" => FRONTENDER_LIBS . "/lessphp",
	"bootstrap" => "lessc.inc.php",
));

Libraries::add("Assetic", array(
	"path" => FRONTENDER_SRC,
	"bootstrap" => false,
));
