<?php
/**
 * RepositoryInspector
 */
use Sledgehammer\Repository;
/**
 * Allows the RepositoryDataSource to inspect the ModelConfig classes in a Repository.
 * @package SledgehammerPlugin
 */
class RepositoryInspector extends Repository {

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
