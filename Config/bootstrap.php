<?php
/**
 * bootstrap
 */
use Sledgehammer\Framework;
/**
 * Initialize the Sledgehammer framework and configure Cake to use Sledgehammer\ErrorHandler
 * @package SledgehammerPlugin
 */
if (!defined('Sledgehammer\INITIALIZED')) {
	define('Sledgehammer\STARTED', TIME_START);
	if (function_exists('posix_getpwuid')) {
		$posix_user = posix_getpwuid(posix_geteuid());
		define('Sledgehammer\TMP_DIR', TMP.'sledgehammer/'.$posix_user['name'].'/');
	} else {
		define('Sledgehammer\TMP_DIR', TMP.'sledgehammer/');
	}
	define('Sledgehammer\VENDOR_DIR', APP.'Vendor/');
	include_once (Sledgehammer\VENDOR_DIR.'autoload.php');
	if (!defined('Sledgehammer\INITIALIZED')) {
		throw new Exception('Sledgehammer not loaded');
	}
}
//define('Sledgehammer\APP_DIR', APP);
if (isset($_SERVER['REQUEST_URI']) && $_SERVER['SCRIPT_FILENAME'] != WWW_ROOT.'test.php') { // A webrequest?
	require_once(Sledgehammer\CORE_DIR.'render_public_folders.php');
}
if ($_SERVER['SCRIPT_FILENAME'] == WWW_ROOT.'test.php') {
	Framework::$autoloader->standalone = false; // PHPUnit also uses an autoloadeder
}
//*
// Don't show notices when a class is unknown to the AutoLoader (allow Cake to load or generate the class)
Framework::$autoloader->standalone = false;
App::uses('CakeModelWrapper', 'Sledgehammer.Model');
/*/
// Register CakePHP and Application classes to Sledgehammer's AutoLoader
$ignoreFiles = array(
	CAKE.'basics.php',
	CAKE.'bootstrap.php',
);
$applicationOverrides = array(
	'Controller/AppController.php',
	'Controller/PagesController.php',
	'Model/AppModel.php',
	'View/Helper/AppHelper.php',
);
// Skip the class in Cake (if an override exists in the app folder
foreach ($applicationOverrides as $applicationOverride) {
	if (file_exists(APP.$applicationOverride)) {
		$ignoreFiles[] = CAKE.$applicationOverride;
	}
}
Framework::$autoLoader->importFolder(CAKE, array(
	'mandatory_superclass' => false,
	'ignore_folders' => array(CAKE.'Console', CAKE.'Test', CAKE.'TestSuite', CAKE.'Config'),
	'ignore_files' => $ignoreFiles,
	// CakePHP doesnt follow it's own standards...
	'matching_filename' => false,
	'one_definition_per_file' => false
));
Framework::$autoLoader->importFolder(APP, array(
	'mandatory_definition' => false,
	'ignore_folders' => array(
		APP.'tmp',
//		APP.'Config',
	),
	// Disable additional checks for vendor scripts
	'detect_accidental_output' => false,
	'matching_filename' => false,
	'mandatory_superclass' => false,
));
// */

// Use the Sledgehammer ErrorHandler
Configure::write('Error.handler', array(Sledgehammer\Framework::$errorHandler, 'errorCallback'));
Configure::write('Error.level', Sledgehammer\E_MAX);

/**
 * An Exception handler callback that reports the exception to SledgeHander before it lets Cake handle the exception.
 *
 * @param Exception $exception
 */
function sledgehammer_plugin_handle_exception_callback($exception) {
	if (headers_sent() === false) {
		$code = $exception->getCode();
		if ($code < 400 || $code >= 506) {
			$code = 500;
		}
		header($_SERVER['SERVER_PROTOCOL'].' '.$code);
	}
	report_exception($exception); // mail/backtrace etc
	ErrorHandler::handleException($exception); // Show the error page (with using CakePHP's Exception.renderer)
}

Configure::write('Exception.handler', 'sledgehammer_plugin_handle_exception_callback');

if (isset($_SERVER['HTTP_DEBUGR'])) {
	$filters = Configure::read('Dispatcher.filters');
	if (is_array($filters) == false) {
		$filters = array();
	}
	$filters[] = 'Sledgehammer.DebugRDispatcher';
	Configure::write('Dispatcher.filters', $filters);
}
?>