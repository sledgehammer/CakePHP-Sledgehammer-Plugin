<?php
/**
 * SledgehammerMysql
 * @package SledgehammerPlugin
 */
App::uses('DboSource', 'Model/Datasource');
App::uses('Mysql', 'Model/Datasource/Database');
/**
 * SledgehammerMysql
 *
 * A DboSource subclass that uses the Sledgehammer's (PDO) Database class.
 * Adds extra debugging information and allows Sledgehammer classes to reuse cake's DATABASE_CONFIG and connections.
 *
 * Caveat:
 *   $db = getDatabase('default');
 * won't work until cake accesses the datasource, use:
 *   ConnectionManager::getDataSource('default');
 * To force a connection.
 */
class SledgehammerMysql extends Mysql {

/**
 * Datasource description
 *
 * @var string
 */
	public $description = "MySQL DBO driver (Sledgehammer edition)";

/**
 * Reference to the Database/PDO object connection
 *
 * @var \Sledgehammer\Database $_connection
 */
	protected $_connection = null;

	function __construct($config = null, $autoConnect = true) {
		unset($this->configKeyName);
		parent::__construct($config, $autoConnect);
	}

	function __set($property, $value) {
		if ($property == 'configKeyName') {
			 \Sledgehammer\Database::$instances[$value] = $this->_connection;
		}
		$this->$property = $value;
	}

/**
 * Connects to the database using options in the given configuration array.
 *
 * @return boolean True if the database could be connected, else false
 * @throws MissingConnectionException
 */
	public function connect() {
		$config = $this->config;
		$this->connected = false;
		try {
			$flags = array(
				PDO::ATTR_PERSISTENT => $config['persistent'],
				PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true,
				PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
			);
			if (!empty($config['encoding'])) {
				$flags[PDO::MYSQL_ATTR_INIT_COMMAND] = 'SET NAMES ' . $config['encoding'];
			}
			if (empty($config['unix_socket'])) {
				$dsn = "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']}";
			} else {
				$dsn = "mysql:unix_socket={$config['unix_socket']};dbname={$config['database']}";
			}
			$this->_connection = new \Sledgehammer\Database(
				$dsn,
				$config['login'],
				$config['password'],
				$flags
			);
			$this->connected = true;
		} catch (PDOException $e) {
			throw new MissingConnectionException(array('class' => $e->getMessage()));
		}

		$this->_useAlias = (bool)version_compare($this->getVersion(), "4.1", ">=");

		return $this->connected;
	}
}
?>
