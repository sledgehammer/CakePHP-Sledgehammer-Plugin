<?php
use SledgeHammer\Inflector;
use SledgeHammer\Collection;
use SledgeHammer\HasManyPlaceholder;
/**
 * CakeModelWrapper present a model instance as an array.
 */
class CakeModelWrapper extends SledgeHammer\Object implements ArrayAccess {

	/**
	 * @var stdClass
	 */
	private $_instance;

	/**
	 * @var Iterator
	 */
	private $_iterator;

	/**
	 * @var array array(
	 *   'model' => string,
	 *	 'resursive' => int Default: 1
	 *
	 */
	private $_options;

	/**
	 * @param stdClass|Collection $data The instance of collection
	 */
	function __construct($data, $options = array()) {
		if ($data instanceof Collection) {
			$this->_instance = false;
			$this->_iterator = $data;
		} else {
			$this->_instance = $data;
			$this->_iterator = new ArrayIterator(self::objectToArray($data));
		}
		$this->_options = $options;
		if (isset($this->_options['model']) == false && $this->_instance) {
			// Autodetect model
			$this->_options['model'] = get_class($data);
			$pos = strrpos($this->_options['model'], '\\');
			if ($pos !== false) {
				$this->_options['model'] = substr($this->_options['model'], $pos + 1);
			}
		}
		if (isset($this->_options['recursive']) == false) {
			$this->_options['recursive'] = 1;
		}
	}

	/**
	 * Generate an array compatible with Model->find('first') & Model->read(null)
	 *
	 * @param int $recursive (optional) Works like Model::find('recursive' => ?)
	 *    -1: Only instance properties
	 *     0: Include belongsTo
	 *     1: Include hasMany
	 *     2: Include the belongsTo of the hasMany
	 *
	 * @return type
	 */
	function toArray($recursive = null) {
		if ($recursive === null) {
			$recursive = $this->_options['recursive'];
		}
		$data = array();
		if ($this->_instance === false) {
			// Collection mode
			$wrapper = null;
			foreach ($this->_iterator as $instance) {
				if ($wrapper === null) {
					$wrapper = new CakeModelWrapper($instance, $this->_options);
				}
				$wrapper->_instance = $instance;
				$data[] = $wrapper->toArray($recursive);
			}
			return $data;
		}
		// Instance mode
		$data[$this->_options['model']] = self::objectToArray($this->_instance, 0);
		if ($recursive < 0) {
			return $data;
		}
		$depth = ($recursive <= 1) ? 0 : 1;

		foreach (get_object_vars($this->_instance) as $property => $value) {
			if (is_object($value)) {
				if ($value instanceof Collection || $value instanceof HasManyPlaceholder) {
					// HasMany
					if ($recursive >= 1) {
						$model = ucfirst(Inflector::singularize($property));
						foreach ($value as $object) {
							$data[$model][]  = self::objectToArray($object, $depth);
						}
					}
				} else {
					// BelongsTo
					$data[ucfirst($property)] = self::objectToArray($value, $depth);
				}
			}
		}
		return $data;
	}

	function offsetGet($offset) {
		if ($this->_instance) {
			if ($offset === $this->_options['model']) {
				return self::objectToArray($this->_instance);
			} else {
				$property = lcfirst(Inflector::pluralize($offset));
				if (property_exists($this->_instance, $property)) {
					$collection = $this->_instance->$property;
					$data = array();
					foreach ($collection as $item) {
						$data[] = self::objectToArray($item);
					}
					return $data;
				}
			}
		} elseif (is_int($offset)) {
			dump($this->_iterator->offsetGet($offset));
		}
		notice('Offset ['.$offset.'] not found');

	}

	function offsetExists($offset) {
		throw new Exception('Not implemented');
	}

	function offsetSet($offset, $value) {
		throw new Exception('Not implemented');
	}

	function offsetUnset($offset) {
		throw new Exception('Not implemented');
	}

	/**
	 * Export all primitive(int, string, enz) properties into an array.
	 *
	 * @param object $object
	 * @return array
	 */
	private static function objectToArray($object, $depth = 0) {
		$data = array();
		foreach (get_object_vars($object) as $property => $value) {
			if (is_object($value)) {
				if ($value instanceof Collection || $value instanceof HasManyPlaceholder) {
					if ($depth > 0) {
						$model = ucfirst(Inflector::singularize($property));
						foreach ($value as $object) {
							$data[$model][]  = self::objectToArray($object, $depth - 1);
						}
					}
				} else {
					$data[$property.'_id'] = $value->id;
					if ($depth > 0) {
						$data[ucfirst($property)] = self::objectToArray($value, $depth - 1);
					}
				}
			} else {
				$data[$property] = $value;
			}
		}
		return $data;
	}

}

?>
