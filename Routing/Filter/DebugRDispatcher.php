<?php
/**
 * DebugRDispatcher
 */
App::uses('DispatcherFilter', 'Routing');
/**
 * DebugRDispatcher
 */
class DebugRDispatcher extends DispatcherFilter {

	public $priority = PHP_INT_MAX;

	 public function afterDispatch($event) {
		 \Sledgehammer\send_headers(array()); // Send DebugR header when the extension is active.
    }
}

?>
