<?php

use lithium\core\Libraries;

/**
 * Setting paths for composer repos or submodule repos
 */

// Submodule Repos
if($options['source'] !== 'composer'){
	$_symfony_path = FRONTENDER_LIBS . "/symfony";
	$_lessc_path = FRONTENDER_LIBS . "/lessphp";
	$_assetic_path = FRONTENDER_SRC;
// Composer vendor/package repos
} else {
	$_symfony_path = LITHIUM_APP_PATH . "/libraries/_source/symfony";
	$_lessc_path = LITHIUM_APP_PATH . "/libraries/_source/joseym/lessphp";
	$_assetic_path = LITHIUM_APP_PATH . "/libraries/_source/kriswallsmith/assetic/src/Assetic";
}

/**
 * Symfony dependancies for Assetic
 */
Libraries::add("Symfony", array(
	"path" => $_symfony_path,
	"bootstrap" => false,
));

/**
 * Less PHP Compiler class
 */
Libraries::add("lessc", array(
	"path" => $_lessc_path,
	"bootstrap" => "lessc.inc.php",
));

/**
 * Assetic Library
 */
Libraries::add("Assetic", array(
	"path" => $_assetic_path,
	"bootstrap" => false,
));
