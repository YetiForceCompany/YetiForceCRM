<?php
/**
 * TreesManager test class
 * @package YetiForce.Test
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 2.0 (licenses/License.html or yetiforce.com)
 * @author Arkadiusz Adach <a.adach@yetiforce.com>
 */
namespace Tests\Settings;

class TreesManager extends \Tests\Init\Base
{

	/**
	 * Array of trees id
	 * @var array()
	 */
	private static $treesId;

	/**
	 * Sequence
	 * @var int
	 */
	private static $seq = 0;

	/**
	 * Testing creation tree
	 * @param int|null $moduleId
	 * @param array() $tree
	 * @dataProvider providerForTree
	 */
	public function testAddTree($moduleId = NULL, $tree = [])
	{
		if (empty($moduleId)) {
			$moduleId = \App\Module::getModuleId('Dashboard');
		}

		$recordModel = new \Settings_TreesManager_Record_Model();
		$recordModel->set('name', 'TestTree' . static::$seq);
		$recordModel->set('module', $moduleId);
		$recordModel->set('tree', $tree);
		$recordModel->set('share', "");
		$recordModel->set('replace', "");
		$recordModel->save();
		static::$treesId[static::$seq] = $recordModel->getId();

		$row = (new \App\Db\Query())->from('vtiger_trees_templates')->where(['templateid' => static::$treesId[static::$seq]])->one();
		$this->assertEquals($row['name'], 'TestTree' . static::$seq);
		$this->assertEquals($row['module'], $moduleId);

		if (count($tree) > 0) {
			$this->assertCount((new \App\Db\Query())->from('vtiger_trees_templates_data')->where(['templateid' => static::$treesId[static::$seq]])->count(), $tree);
		}
		static::$seq++;
	}

	/**
	 * Data provider for testAddTree and testEditTree
	 * @return array
	 * @codeCoverageIgnore
	 */
	public function providerForTree()
	{
		$tree1[] = $this->createItemForTree('item1', 1);
		$tree1[] = $this->createItemForTree('item2', 2);
		return [
			[NULL, []],
			[NULL, $tree1],
		];
	}

	/**
	 * Create item for tree array
	 * @param string $itemName
	 * @param int $id
	 * @return array()
	 */
	private function createItemForTree($itemName, $id)
	{
		return [
			'id' => $id,
			'text' => $itemName,
			'icon' => '1',
			'li_attr' => ['id' => $id],
			'a_attr' => ['href' => '#', 'id' => $id . '_anchor'],
			'state' => ['loaded' => '1', 'opened' => false, 'selected' => false, 'disabled' => false],
			'data' => [],
			'children' => [],
		];
	}
}
