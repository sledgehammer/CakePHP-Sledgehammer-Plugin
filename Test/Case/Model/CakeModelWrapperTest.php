<?php
App::uses('Customer', 'Model');
App::uses('Order', 'Model');

/**
 * CakeModelWrapper  Test Case
 *
 */
class CakeModelWrapperTestCase extends CakeTestCase {
/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array('app.customer', 'app.order');

/**
 * setUp method
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();

		// Reset DB & Repository
		SledgeHammer\Repository::$instances = array();
		SledgeHammer\Database::$instances = array(
			'test' => new SledgeHammer\Database('sqlite:/tmp/cakephp_test.sqlite'),
		);
		$db = SledgeHammer\getDatabase('test');
		$sql = $db->fetchValue("SELECT sql FROM sqlite_master WHERE type='table' AND tbl_name='orders'");
		$db->query('ALTER TABLE orders RENAME TO orders_backup');
		$createStatement = substr(trim($sql), 0, -1).",\n\tFOREIGN KEY (customer_id) REFERENCES customers (id)\n)";
		$db->query($createStatement);
		$db->query('INSERT INTO orders SELECT * FROM orders_backup');
		$db->query('DROP TABLE orders_backup');
		try {
			ConnectionManager::getDataSource('test')->query('SELECT * FROM orders');
			$this->fail('No "SQLSTATE[HY000]: General error: 17 database schema has changed" error?');
		} catch (Exception $e) {
			$this->assertEquals($e->getMessage(), 'SQLSTATE[HY000]: General error: 17 database schema has changed');
		}
		$backend = new SledgeHammer\DatabaseRepositoryBackend('test');
		$backend->configs['Customer']->class = 'stdClass';
		$backend->configs['Order']->class = 'stdClass';
		SledgeHammer\getRepository()->registerBackend($backend);

		$this->Customer = ClassRegistry::init('Customer');
		$this->Order = ClassRegistry::init('Order');
	}

	function test_find_first() {
		$this->compareResults($this->Customer, '1', -1);
		$this->compareResults($this->Customer, '1', 0);
		$this->compareResults($this->Customer, '1', 1);
		$this->compareResults($this->Customer, '1', 2);

		$this->compareResults($this->Order, '1', -1);
		$this->compareResults($this->Order, '1', 0);
		$this->compareResults($this->Order, '1', 1);
		$this->compareResults($this->Order, '1', 2);
	}

	/**
	 *
	 * @param AppModel $model
	 * @param int|string $id
	 * @param int $recursive
	 */
	private function compareResults($model, $id, $recursive = 2) {
		$cakeResult = $model->find('first', array(
			'condition' => array(
				$model->alias.'.id' => 1
			),
			'recursive' => $recursive
		));

		$repo = SledgeHammer\getRepository();
		$instance = $repo->get($model->alias, $id);
		$wrapped = new CakeModelWrapper($instance, array('model' => $model->alias));

//		dump($recursive);
//		dump($cakeResult);
//		dump($wrapped->toArray($recursive));
		ob_flush();
		$this->assertEquals($cakeResult, $wrapped->toArray($recursive));

	}

/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown() {
		unset($this->Customer);

		parent::tearDown();
	}

}
