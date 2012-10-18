<?php
/**
 * SledgehammerMysql
 */
App::uses('DboSource', 'Model/Datasource');
App::uses('Mysql', 'Model/Datasource/Database');
/**
 * A DboSource subclass that uses the Sledgehammer's (PDO) Database class.
 * Adds extra debugging information and allows Sledgehammer classes to reuse cake's DATABASE_CONFIG and connections.
 *
 * Caveat:
 *   $db = getDatabase('default');
 * won't work until cake accesses the datasource, to force a connection use:
 *   ConnectionManager::getDataSource('default');
 *
 * @package SledgehammerPlugin
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
		if (class_exists('DATABASE_CONFIG', false)) {
			$dbConfigs = new DATABASE_CONFIG();
			foreach ($dbConfigs as $link => $dbConfig) {
				if ($config == $dbConfig) {
					$config['identifier'] = $link;
					break;
				}
			}
		}
		parent::__construct($config, $autoConnect);
	}

	function __set($property, $value) {
        if ($property == 'configKeyName') {
			if ($this->config['identifier'] !== $value) {
				warning('The detected identifier "'.$this->config['identifier'].'" was incorrect and should have been "'.$value.'"', 'Identical database configurations?');
				if (empty(\Sledgehammer\Database::$instances[$value])) {
					\Sledgehammer\Database::$instances[$value] = $this->_connection;
				}
			}
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
			if (empty($config['unix_socket'])) {
				$dsn = "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']}";
			} else {
				$dsn = "mysql:unix_socket={$config['unix_socket']};dbname={$config['database']}";
			}
			if (!empty($config['encoding'])) {
				$dsn .= ';charset='.$config['encoding'];
			}
			if (!empty($config['identifier'])) {
				$flags['logIdentifier'] = ($config['identifier'] === 'default') ? 'Database' : 'Database[<b>'.$config['identifier'].'</b>]';
			}
			$this->_connection = new \Sledgehammer\Database(
				$dsn,
				$config['login'],
				$config['password'],
				$flags
			);
			if (file_exists(Sledgehammer\APP_DIR.'database.ini') === false) { // CakePHP handles the database connections?
				\Sledgehammer\Database::$instances[$config['identifier']] = $this->_connection;
			}
			$this->connected = true;
		} catch (PDOException $e) {
			throw new MissingConnectionException(array('class' => $e->getMessage()));
		}

		$this->_useAlias = (bool)version_compare($this->getVersion(), "4.1", ">=");

		return $this->connected;
	}
}
?>
