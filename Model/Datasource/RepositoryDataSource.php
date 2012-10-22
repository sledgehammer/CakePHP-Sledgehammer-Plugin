<?php
/**
 * RepositoryDataSource
 */
App::uses('DataSource', 'Model/Datasource');
App::uses('RepositoryInspector', 'Sledgehammer.Model/Datasource');
/**
 * DataSource adapter to use Sledgehammer\s Repository as backend for AppModels
 * @package SledgehammerPlugin
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
			$repo = \Sledgehammer\getRepository($this->repository);
			$models = array_keys(RepositoryInspector::getModelConfig($repo));
			foreach ($models as $model) {
				$data[] = Inflector::tableize($model);
			}
		}
		return parent::listSources($data);
	}

	function describe($model) {
		if (empty($this->_descriptions)) {
			$repo = \Sledgehammer\getRepository($this->repository);
			$sources = $this->listSources();
			foreach ($sources as $table) {
				$config = RepositoryInspector::getModelConfig($repo, Sledgehammer\Inflector::modelize($table, array('singularizeLast' => true)));
				foreach ($config->belongsTo as $property => $belongsTo) {
					$this->_descriptions[$table][$property.'_id'] = array();
				}
				foreach ($config->properties as $column => $property) {
					$this->_descriptions[$table][$property] = array(
						'type' => null,
						'length' => null,
					);
				}
				// Move ID to the beginning of the array.
				$id = $this->_descriptions[$table][$config->id[0]];
				unset($this->_descriptions[$table][$config->id[0]]);
				Sledgehammer\array_key_unshift($this->_descriptions[$table], $config->id[0], $id);
			}
		}
		return parent::describe($model);
	}

	function calculate(Model $Model, $type) {
		if ($type === 'count') {
			return '__COUNT__';
		}
		warning('Calculation type: "'.$type.'" not suported');
	}

	function create(Model $Model, $fields = null, $values = null) {
		$repo = \Sledgehammer\getRepository($this->repository);
		$instance = $repo->create($this->resolveModel($Model));
		$this->importData($Model, array_combine($fields, $values), $instance);
		$repo->save($this->resolveModel($Model), $instance);
		return true;
	}

	/**
	 * Retrieving (& filtering & sorting) data
	 *
	 * @param Model $Model
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
	function read(Model $Model, $queryData = array(), $recursive = NULL) {
		$repo = \Sledgehammer\getRepository($this->repository);
		$result = $repo->all($this->resolveModel($Model));
		$conditions = array();
		if ($queryData['conditions'] !== null) {
			foreach ($queryData['conditions'] as $column => $value) {
				if (\Sledgehammer\text($column)->startsWith($Model->alias.'.')) {
					$column = substr($column, strlen($Model->alias) + 1); // Remove alias
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
					if (\Sledgehammer\text($column)->startsWith($Model->alias.'.')) {
						$column = substr($column, strlen($Model->alias) + 1); // Remove alias
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

	/**
	 * Save changes.
	 *
	 * @param Model $Model
	 * @param array $fields
	 * @param array $values
	 * @return boolean
	 */
	function update(Model $Model, $fields = null, $values = null, $conditions = null) {
		$repo = \Sledgehammer\getRepository($this->repository);
		$instance = $repo->get($this->resolveModel($Model), $Model->id);
		$this->importData($Model, array_combine($fields, $values), $instance);
		$repo->save($this->resolveModel($Model), $instance);
		return true;
	}

	public function delete(Model $Model, $id = null) {
		if (is_array($id)) {
			foreach ($id as $column => $value) {
				if (\Sledgehammer\text($column)->startsWith($Model->alias.'.')) {
					unset($id[$column]);
					$id[substr($column, strlen($Model->alias) + 1)] = $value; // Remove alias
				}
			}
		}
		$repo = \Sledgehammer\getRepository($this->repository);
		$repo->delete(get_class($Model), $id);
		return true;
	}

	private function importData(Model $Model, $data, $instance) {
		$repo = \Sledgehammer\getRepository($this->repository);
		$config = RepositoryInspector::getModelConfig($repo, $this->resolveModel($Model));
		foreach ($data as $field => $value) {
			if (property_exists($instance, $field)) {
				$instance->$field = $value;
				continue;
			}
			if (substr($field, -3) === '_id' && property_exists($instance, substr($field, 0, -3))) { // BelongTo?
				$property =  substr($field, 0, -3);
				if ($value === null) {
					$instance->$property = null;
				} elseif ($instance->$property !== null && $instance->$property->id === $value) {
					continue; // relation unchanged
				}
				$instance->$property = $repo->get($config->belongsTo[$property]['model'], $value);
			} else {
				notice('Ignoring field "'.$field.'"', array('Value' => $value));
			}
		}
	}

	private function resolveModel(Model $Model) {
		return (Sledgehammer\Inflector::modelize($Model->table, array('singularizeLast' => true)));
	}

}

?>
