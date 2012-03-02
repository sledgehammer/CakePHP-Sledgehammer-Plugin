<?php
use SledgeHammer\Framework;
/**
 * Initialize the SledgeHammer framework
 * and configure Cake to use SledgeHammer\ErrorHandler
 *
 * @package SledgeHammerPlugin
 */
if (defined('SledgeHammer\INITIALIZED')) {
	return;
}
if (file_exists(ROOT.'/sledgehammer/core/init_framework.php') === false) {
	trigger_error('SledgeHammer Framework not found in "'.ROOT.'/sledgehammer/"', E_USER_WARNING);
	return;
}
define('SledgeHammer\MICROTIME_START', TIME_START);
if (isset($_SERVER['REQUEST_URI']) && $_SERVER['SCRIPT_FILENAME'] != WWW_ROOT.'test.php') { // A webrequest?
	require_once(ROOT.'/sledgehammer/core/render_public_folders.php');
}
if (function_exists('posix_getpwuid')) {
	$posix_user = posix_getpwuid(posix_geteuid());
	define('SledgeHammer\TMP_DIR', TMP.'sledgehammer/'.$posix_user['name'].'/');
} else {
	define('SledgeHammer\TMP_DIR', TMP.'sledgehammer/');

}
require_once(ROOT.'/sledgehammer/core/init_framework.php');
if ( $_SERVER['SCRIPT_FILENAME'] == WWW_ROOT.'test.php') {
	Framework::$autoLoader->standalone = false; // PHPUnit also uses an autoloadeder
}
//*
// Don't show notices when a class is unknown to the AutoLoader (allow Cake to load the class)
Framework::$autoLoader->standalone = false;
/*/
// Register CakePHP and Application classes to SledgeHammer's AutoLoader
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

// Don't override the SlegdeHammer ErrorHandler
Configure::write('Error', array());

/**
 * An Exception handler callback that reports the exception to SledgeHander before it lets Cake handle the exception.
 *
 * @param Exception $exception
 */
function handle_exception_callback($exception) {
	SledgeHammer\ErrorHandler::handle_exception($exception); // mail/backtrace etc
	ErrorHandler::handleException($exception); // Show 404/500 error page (with the CakePHP ErrorHandler)
}

Configure::write('Exception', array(
	'handler' => 'handle_exception_callback',
	'renderer' => 'ExceptionRenderer',
	'log' => true
));
?>
