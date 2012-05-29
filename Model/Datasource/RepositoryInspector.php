<?php
/**
 * RepositoryInspector
 * @package SledgehammerPlugin
 */

/**
 * Allows the RepositoryDataSource to inspect the ModelConfig classes in a Repository.
 */
class RepositoryInspector extends \Sledgehammer\Repository {

	/**
	 *
	 * @param \Sledgehammer\Repository $repository
	 * @param string $model
	 * @return \Sledgehammer\ModelConfig
	 */
	static function getModelConfig($repository, $model) {
		return $repository->_getConfig($model);
	}
}

?>
