<?php
/**
 * CakeModelWrapperTestCase
 * @package SledgehammerPlugin
 */
require_once(CAKE_CORE_INCLUDE_PATH.'/Cake/Test/Case/Model/models.php');
/**
 * CakeModelWrapper  TestCase
 */
class CakeModelWrapperTestCase extends CakeTestCase {

	/**
	 * Fixtures
	 *
	 * @var array
	 */
	public $fixtures = array('core.Home', 'core.AnotherArticle', 'core.Advertisement');

	/**
	 * setUp method
	 *
	 * @return void
	 */
	public function setUp() {
		parent::setUp();

		$filename = Sledgehammer\Framework::$autoLoader->getFilename('Sledgehammer\Repository');
		if ($filename === null) {
			$this->markTestSkipped('Sledgehammer ORM module not installed');
		}

		// Reset DB & Repository
		Sledgehammer\Repository::$instances = array();
		Sledgehammer\Database::$instances = array(
			'test' => new Sledgehammer\Database('sqlite:/tmp/cakephp_test.sqlite'),
		);
		$db = Sledgehammer\getDatabase('test');
		$foreignKeys = $db->query('PRAGMA foreign_key_list(Homes)');
		if (count($foreignKeys->fetchAll()) == 0) {
			// Add missing foreign_key
			$sql = $db->fetchValue("SELECT sql FROM sqlite_master WHERE type='table' AND tbl_name='homes'");
			$db->query('ALTER TABLE homes RENAME TO homes_backup');
			$createStatement = substr(trim($sql), 0, -1).",\n\t";
			$createStatement .= "FOREIGN KEY (advertisement_id) REFERENCES advertisements (id),\n\t";
			$createStatement .= "FOREIGN KEY (another_article_id) REFERENCES another_articles (id))";
			$db->query($createStatement);
			$db->query('INSERT INTO homes SELECT * FROM homes_backup');
			$db->query('DROP TABLE homes_backup');
			try {
				ConnectionManager::getDataSource('test')->query('SELECT * FROM homes');
				$this->fail('No "SQLSTATE[HY000]: General error: 17 database schema has changed" error?');
			} catch (Exception $e) {
				$this->assertEquals($e->getMessage(), 'SQLSTATE[HY000]: General error: 17 database schema has changed');
			}
		}
		$backend = new Sledgehammer\DatabaseRepositoryBackend('test');
		$backend->configs['Home']->class = 'stdClass';
		$backend->configs['Advertisement']->class = 'stdClass';
		$backend->configs['AnotherArticle']->class = 'stdClass';
		Sledgehammer\getRepository()->registerBackend($backend);

		$this->Advertisement = ClassRegistry::init('Advertisement');
		$this->Home = ClassRegistry::init('Home');
	}

	function test_find_first() {
		$this->compareFindFirst($this->Advertisement, '1', -1);
		$this->compareFindFirst($this->Advertisement, '1', 0);
		$this->compareFindFirst($this->Advertisement, '1', 1);
		$this->compareFindFirst($this->Advertisement, '1', 2);

		$this->compareFindFirst($this->Home, '1', -1);
		$this->compareFindFirst($this->Home, '1', 0);
		$this->compareFindFirst($this->Home, '1', 1);
		$this->compareFindFirst($this->Home, '1', 2);
	}

	function test_find_all() {
		$this->compareFindAll($this->Advertisement, -1);
		$this->compareFindAll($this->Advertisement, 0);
		$this->compareFindAll($this->Advertisement, 1);
		$this->compareFindAll($this->Advertisement, 2);

		$this->compareFindAll($this->Home, -1);
		$this->compareFindAll($this->Home, 0);
		$this->compareFindAll($this->Home, 1);
		$this->compareFindAll($this->Home, 2);
		ob_flush();
	}

	function test_offset_exist_in_instance() {
		$repo = Sledgehammer\getRepository();
		$wrapped = new CakeModelWrapper($repo->getHome(1), array('model' => 'Home'));
		$this->assertTrue($wrapped->offsetExists('Advertisement'));
		$this->assertFalse($wrapped->offsetExists('BlaBla'));
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
			'conditions' => array(
				$model->alias.'.id' => 1
			),
			'recursive' => $recursive
		));

		$repo = Sledgehammer\getRepository();
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

		$repo = Sledgehammer\getRepository();
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
		unset($this->Advertisement);

		parent::tearDown();
	}

}
