<?php
App::uses('Customer', 'Model');
App::uses('Order', 'Model');
/**
 * CakeModelWrapper  Test Case
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
		$foreignKeys = $db->query('PRAGMA foreign_key_list(orders)');
		if (count($foreignKeys->fetchAll()) == 0) {
			// Add missing foreign_key
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
		}
		$backend = new SledgeHammer\DatabaseRepositoryBackend('test');
		$backend->configs['Customer']->class = 'stdClass';
		$backend->configs['Order']->class = 'stdClass';
		SledgeHammer\getRepository()->registerBackend($backend);

		$this->Customer = ClassRegistry::init('Customer');
		$this->Order = ClassRegistry::init('Order');
	}

	function test_find_first() {
		$this->compareFindFirst($this->Customer, '1', -1);
		$this->compareFindFirst($this->Customer, '1', 0);
		$this->compareFindFirst($this->Customer, '1', 1);
		$this->compareFindFirst($this->Customer, '1', 2);

		$this->compareFindFirst($this->Order, '1', -1);
		$this->compareFindFirst($this->Order, '1', 0);
		$this->compareFindFirst($this->Order, '1', 1);
		$this->compareFindFirst($this->Order, '1', 2);
	}

	function test_find_all() {
		$this->compareFindAll($this->Customer, -1);
		$this->compareFindAll($this->Customer, 0);
		$this->compareFindAll($this->Customer, 1);
		$this->compareFindAll($this->Customer, 2);

		$this->compareFindAll($this->Order, -1);
		$this->compareFindAll($this->Order, 0);
		$this->compareFindAll($this->Order, 1);
		$this->compareFindAll($this->Order, 2);
		ob_flush();
	}

	/**
	 * Compare the output from an Model->find('first') with the output from CakeModelWrapper->toArray()
	 *
	 * @param AppModel $model
	 * @param int|string $id
	 * @param int $recursive
	 */
	private function compareFindFirst($model, $id, $recursive) {
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
		$this->assertEquals($cakeResult, $wrapped->toArray($recursive));
	}

	/**
	 * Compare the output from an Model->find('all') with the output from CakeModelWrapper->toArray()
	 *
	 * @param AppModel $model
	 * @param int $recursive
	 */
	private function compareFindAll($model, $recursive) {
		$cakeResult = $model->find('all', array(
			'recursive' => $recursive
		));

		$repo = SledgeHammer\getRepository();
		$collection = $repo->all($model->alias);
		$wrapped = new CakeModelWrapper($collection, array('model' => $model->alias));

//		dump($recursive);
//		dump($cakeResult);
//		dump($wrapped->toArray($recursive));
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
