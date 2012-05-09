<?php
/**
 * RepositoryInspector
 */
class RepositoryInspector extends \SledgeHammer\Repository {

	/**
	 *
	 * @param \SledgeHammer\Repository $repository
	 * @param string $model
	 * @return \SledgeHammer\ModelConfig
	 */
	static function getModelConfig($repository, $model) {
		return $repository->_getConfig($model);
	}
}

?>
