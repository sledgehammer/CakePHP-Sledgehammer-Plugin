<?php
App::uses('DataSource', 'Model/Datasource');
/**
 * DataSource adapter to use SledgeHammer\s Repository as backend for AppModels
 */
class RepositoryDataSource extends DataSource {

	private $repository = 'default';

	function __construct($config = array()) {
		if (isset($config['repository'])) {
			$this->repository = $config['repository'];
		}
		$config['database'] = '';
		parent::__construct($config);
	}

	/**
	 * List all Models from the app/Model/ directory that are available in the Repository
	 *
	 * @param array $data
	 * @return array
	 */
	function listSources($data = null) {
		if ($data === null && $this->_sources === null) {
			$repo = \SledgeHammer\getRepository($this->repository);
			$dir = new DirectoryIterator(APP.'Model');
			$data = array();
			foreach ($dir as $entry) {
				if ($entry->isFile()) {
					$model = substr($entry->getFilename(), 0, -4);
					if ($repo->isConfigured($model)) {
						$data[] = Inflector::tableize($model);
					}
				}
			}
		}
		return parent::listSources($data);
	}

	function describe($model) {
		if (empty($this->_descriptions)) {
			$sources = $this->listSources();
			foreach ($sources as $table) {
				$this->_descriptions[$table] = array(
					'id' => array(), // assume 'id' column
				);
			}
		}
		return parent::describe($model);
	}

	function calculate(Model $model, $type) {
		if ($type === 'count') {
			return '__COUNT__';
		}
		warning('Calculation type: "'.$type.'" not suported');
	}

	/**
	 * Retrieving (& filtering & sorting) data
	 *
	 * @param Model $model
	 * @param array $queryData array(
	 *   'conditions' => array(), PARTIAL SUPPORT,
	 *   'fields' => NULL,        IGNORED
	 *   'joins' => array(),      IGNORED
	 *   'limit' => NULL,         SUPPORTED
	 *   'offset' => NULL,        SUPPORTED
	 *   'order' => array(NULL),  SUPPORTED
	 *   'page' => 1,             IGNORED
	 *   'group' => NULL,         IGNORED
	 *   'callbacks' => true,     IGNORED
	 *   'recursive',             IGNORED
	 *   'list',                  IGNORED
	 * )
	 * @return \CakeModelWrapper
	 */
	function read(Model $model, $queryData = array()) {
		$repo = \SledgeHammer\getRepository($this->repository);
		$result = $repo->all(get_class($model));
		$conditions = array();
		if ($queryData['conditions'] !== null) {
			foreach ($queryData['conditions'] as $column => $value) {
				if (\SledgeHammer\text($column)->startsWith($model->alias.'.')) {
					$column = substr($column, strlen($model->alias) + 1); // Remove alias
				}
				$conditions[$column] = $value;
			}
		}
		if (count($conditions) !== 0) {
			$result = $result->where($conditions);
		}
		foreach (array_reverse($queryData['order']) as $order) {
			if ($order) {
				foreach ($order as $column => $direction) {
					if (\SledgeHammer\text($column)->startsWith($model->alias.'.')) {
						$column = substr($column, strlen($model->alias) + 1); // Remove alias
					}

					if (strcasecmp($direction, 'asc') === 0) {
						$result = $result->orderBy($column);
					} elseif (strcasecmp($direction, 'desc') === 0) {
						$result = $result->orderByDescending($column);
					} else {
						notice('order format not supported');
					}
				}
			}
		}
		if ($queryData['offset'] !== null) {
			$result = $result->skip($queryData['offset']);
		}
		if ($queryData['limit'] !== null) {
			$result = $result->take($queryData['limit']);
		}
		if ($queryData['fields'] === '__COUNT__') {
			return array(array(array('count' => $result->count())));
		}
		$wrapper = new CakeModelWrapper($result);
		return $wrapper;
	}

}

?>
