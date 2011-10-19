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
define('SledgeHammer\TMP_DIR', TMP.'sledgehammer/');
require_once (ROOT.'/sledgehammer/core/init_framework.php');
//* 
// Don't show notices when a class is unknown to the AutoLoader (allow Cake to load the class)
$GLOBALS['AutoLoader']->standalone = false;
/* /
  // Register CakePHP and Application classes to the AutoLoader
  $GLOBALS['AutoLoader']->importFolder(CAKE, array(
  'mandatory_definition' => false,
  'mandatory_superclass' => false,
  'matching_filename' => false,
  'detect_accidental_output'=> false,
  'one_definition_per_file' => false,
  'ignore_folders' => array(CAKE.'Console', CAKE.'Test'),
  ));
  $GLOBALS['AutoLoader']->importFolder(APP, array(
  'mandatory_definition' => false,
  'detect_accidental_output'=> false,
  'matching_filename' => false,
  'mandatory_superclass' => false,
  ));
  // */

// Enable the SlegdeHammer ErrorHandler
Configure::write('Error', array());

/**
 * An Exception handler callback that reports the exception to SledgeHander before it lets Cake handle the exception.
 * 
 * @param Exception $exception 
 */
function handle_exception_callback($exception) {
	SledgeHammer\ErrorHandler::handle_exception($exception);
	ErrorHandler::handleException($exception);
}
Configure::write('Exception', array(
	'handler' => 'handle_exception_callback',
	'renderer' => 'ExceptionRenderer',
	'log' => true
));
?>
