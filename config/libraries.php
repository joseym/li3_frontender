<?php

use lithium\core\Libraries;

/**
 * Symfony dependancies for Assetic
 */
Libraries::add("Symfony", array(
	"path" => FRONTENDER_LIBS . "/symfony",
	"bootstrap" => false,
));

/**
 * Less PHP Compiler class
 */
Libraries::add("lessc", array(
	"path" => FRONTENDER_LIBS . "/lessphp",
	"bootstrap" => "lessc.inc.php",
));

/**
 * Assetic Library
 */
Libraries::add("Assetic", array(
	"path" => FRONTENDER_SRC,
	"bootstrap" => false,
));
