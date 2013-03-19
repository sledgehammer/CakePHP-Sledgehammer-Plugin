<?php
/**
 * Register CakePHP and Application classes to Sledgehammer's AutoLoader.
 */
use Sledgehammer\Framework;
if (!defined('Sledgehammer\STARTED')) {
	define('Sledgehammer\VENDOR_DIR', dirname(__DIR__).'/../Vendor/');
	include_once (Sledgehammer\VENDOR_DIR.'autoload.php');
}
$app = realpath(dirname(__DIR__ ).'/../').'/';
$root = dirname($app);
$cake = $root.'/lib/Cake/';

Framework::$autoloader->importFolder($cake, array(
	'mandatory_superclass' => false,
	'ignore_folders' => array(
		$cake.'Console',
		$cake.'Test',
		$cake.'TestSuite',
		$cake.'Config'
	),
	'ignore_files' => array(
		$cake.'basics.php',
		$cake.'bootstrap.php',
	),
	// CakePHP doesnt follow it's own standards...
	'matching_filename' => false,
	'one_definition_per_file' => false
));
Framework::$autoloader->importFolder($app, array(
	'mandatory_definition' => false,
	'ignore_folders' => array(
		$app.'Vendor', // Already imported
		$app.'tmp',
		$app.'webroot',
//		$app.'Config',
	),
	// Disable additional checks for Plugin scripts
	'detect_accidental_output' => false,
	'matching_filename' => false,
	'mandatory_superclass' => false,
));
?>
