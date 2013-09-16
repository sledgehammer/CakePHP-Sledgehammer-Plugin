<?php
/**
 * DebugRDispatcher
 */
use Sledgehammer\DebugR;
App::uses('DispatcherFilter', 'Routing');
/**
 * DebugRDispatcher
 *
 * @package SledgehammerPlugin
 */
class DebugRDispatcher extends DispatcherFilter {

	public $priority = PHP_INT_MAX;

	/**
	 * Send DebugR-sledgehammer-statusbar header when the extension is active.
	 * @param CakeEvent $event
	 */
	 public function afterDispatch(CakeEvent $event) {
		 if (DebugR::isEnabled() && headers_sent() === false) {
			ob_start();
			Sledgehammer\statusbar();
			DebugR::send('sledgehammer-statusbar', ob_get_clean(), true);
		}
    }
}

?>
