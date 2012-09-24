<?php
/**
 * DebugRDispatcher
 */
use Sledgehammer\DebugR;
App::uses('DispatcherFilter', 'Routing');
/**
 * DebugRDispatcher
 */
class DebugRDispatcher extends DispatcherFilter {

	public $priority = PHP_INT_MAX;

	/**
	 * Send DebugR-sledgehammer-statusbar header when the extension is active.
	 * @param type $event
	 */
	 public function afterDispatch($event) {
		 if (DebugR::isEnabled() && headers_sent() === false) {
			ob_start();
			Sledgehammer\statusbar();
			DebugR::send('sledgehammer-statusbar', ob_get_clean(), true);
		}
    }
}

?>