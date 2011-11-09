<?php
/**
 * Initialize the SledgeHammer framework
 * and configure Cake to use SledgeHammer\ErrorHandler
 *
 * @package SledgeHammerPlugin
 */
if (defined('SledgeHammer\INITIALIZED')) {
	return;
}
define('SledgeHammer\MICROTIME_START', TIME_START);
require_once (ROOT.'/sledgehammer/core/render_public_folders.php');

define('SledgeHammer\TMP_DIR', TMP.'sledgehammer/');
require_once (ROOT.'/sledgehammer/core/init_framework.php');

/*
// Don't show notices when a class is unknown to the AutoLoader (allow Cake to load the class)
$GLOBALS['AutoLoader']->standalone = false;
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
$GLOBALS['AutoLoader']->importFolder(CAKE, array(
	'mandatory_superclass' => false,
	'ignore_folders' => array(CAKE.'Console', CAKE.'Test'),
	'ignore_files' => $ignoreFiles,
));
$GLOBALS['AutoLoader']->enableCache = false;
$GLOBALS['AutoLoader']->importFolder(APP, array(
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
	ErrorHandler::handleException($exception); // Show 404/500 error page
}

Configure::write('Exception', array(
	'handler' => 'handle_exception_callback',
	'renderer' => 'ExceptionRenderer',
	'log' => true
));
?>
