<?php
/**
 * CakeModelWrapper
 * @package SledgehammerPlugin
 */
use Sledgehammer\Inflector;
use Sledgehammer\Collection;
use Sledgehammer\HasManyPlaceholder;
use Sledgehammer\BelongsToPlaceholder;
/**
 * Adaptor for ORM objects to CakePHP output.
 * Behaves like an array the FormHelper & scaffolding classes expect.
 */
class CakeModelWrapper extends Sledgehammer\Object implements ArrayAccess, Iterator {

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
	 * The instance or collection
	 * @param stdClass|Collection $data
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
	 * @return array
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
						$data[$model] = array();
						foreach ($value as $object) {
							$data[$model][]  = self::objectToArray($object, $depth);
						}
					}
				} else {
					// BelongsTo
					if ($value instanceof BelongsToPlaceholder) {
						$value->id = $value->id; // replace placeholder
						$value = $this->_instance->$property;
					}
					$data[Inflector::camelCase($property)] = self::objectToArray($value, $depth);
				}
			}
		}
		return $data;
	}

	// ArrayAccess interface

	function offsetGet($offset) {
		if ($this->_instance) {
			if ($offset === $this->_options['model']) {
				return self::objectToArray($this->_instance);
			} else {
				$property = lcfirst($offset);
				if (property_exists($this->_instance, $property)) { // BelongsTo relation?
					$this->_instance->$property->id = $this->_instance->$property->id; // Replaces placeholder
					return self::objectToArray($this->_instance->$property);
				}
				$property = lcfirst(Inflector::pluralize($offset));
				if (property_exists($this->_instance, $property)) { // HasMany relation?
					$collection = $this->_instance->$property;
					$data = array();
					foreach ($collection as $item) {
						$data[] = self::objectToArray($item);
					}
					return $data;
				}
			}
		} elseif (is_int($offset)) {
			return new CakeModelWrapper($this->_iterator->offsetGet($offset), $this->_options);
		}
		notice('Offset ['.$offset.'] not found');
	}

	function offsetExists($offset) {
		if ($this->_instance) {
			if ($offset === $this->_options['model']) {
				return true;
			}
			foreach (get_object_vars($this->_instance) as $property => $value) {
				if (is_object($value)) {
					if ($value instanceof Collection || $value instanceof HasManyPlaceholder) {
						$model = ucfirst(Inflector::singularize($property));
					} else {
						$model = ucfirst($property);
					}
					if ($offset === $model) {
						return true;
					}
				}
			}
			return false;
		} else {
			return $this->_iterator->offsetExists($offset);
		}
	}

	function offsetSet($offset, $value) {
		throw new Exception('Not implemented');
	}

	function offsetUnset($offset) {
		throw new Exception('Not implemented');
	}

	// Iterator interface

	public function current() {
		// @todo Return the same CakeModelWrapper when the instance is the same. (when ->current() is called twice)
		return new CakeModelWrapper($this->_iterator->current(), $this->_options);
	}

	public function key() {
		return $this->_iterator->key();
	}

	public function next() {
		return $this->_iterator->next();
	}

	public function rewind() {
		return $this->_iterator->rewind();
	}

	public function valid() {
		return $this->_iterator->valid();
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
					// HasMany
					if ($depth > 0) {
						$model = ucfirst(Inflector::singularize($property));
						foreach ($value as $object) {
							$data[$model][]  = self::objectToArray($object, $depth - 1);
						}
					}
				} else {
					// BelongsTo
					$data[$property.'_id'] = $value->id;
					if ($depth > 0) {
						if ($value instanceof BelongsToPlaceholder) {
							$value->id = $value->id; // Replaces placeholder
							$value = $object->$property;
						}
						$data[Inflector::camelCase($property)] = self::objectToArray($value, $depth - 1);
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
