<?php

/**
 * Inventory test class
 * @package YetiForce.Test
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 2.0 (licenses/License.html or yetiforce.com)
 * @author Arkadiusz Adach <a.adach@yetiforce.com>
 */
use PHPUnit\Framework\TestCase;

/**
 * @covers Inventory::<public>
 */
class Inventory extends TestCase
{

	/**
	 * Inventory id
	 */
	static $id;

	/**
	 * Testing inventory creation
	 */
	public function testAddInventory()
	{
		$type = 'Taxes';
		$recordModel = new Settings_Inventory_Record_Model();

		$recordModel->set('id', '');
		$recordModel->set('name', 'testowy podatek');
		$recordModel->set('value', 3.14);
		$recordModel->set('status', 0);
		$recordModel->setType($type);
		static::$id = $recordModel->save();

		$tableName = Settings_Inventory_Record_Model::getTableNameFromType($type);
		$row = (new \App\Db\Query())->from($tableName)->where(['id' => static::$id])->one();
		$this->assertNotFalse($row, 'No record id: ' . static::$id);
		$this->assertEquals($row['name'], 'testowy podatek');
		$this->assertEquals($row['value'], 3.14);
		$this->assertEquals($row['status'], 0);
	}

	/**
	 * Testing inventory edition
	 */
	public function testEditInventory()
	{
		$type = 'Taxes';
		$recordModel = Settings_Inventory_Record_Model::getInstanceById(static::$id, $type);

		$recordModel->set('id', static::$id);
		$recordModel->set('name', 'testowy podatek edy');
		$recordModel->set('value', 1.16);
		$recordModel->set('status', 1);
		$recordModel->setType($type);
		$recordModel->save();

		$tableName = Settings_Inventory_Record_Model::getTableNameFromType($type);
		$row = (new \App\Db\Query())->from($tableName)->where(['id' => static::$id])->one();
		$this->assertNotFalse($row, 'No record id: ' . static::$id);
		$this->assertEquals($row['name'], 'testowy podatek edy');
		$this->assertEquals($row['value'], 1.16);
		$this->assertEquals($row['status'], 1);
	}

	/**
	 * Testing inventory deletion
	 */
	public function testDeleteInventory()
	{
		$type = 'Taxes';
		$recordModel = Settings_Inventory_Record_Model::getInstanceById(static::$id, $type);
		$recordModel->delete();

		$tableName = Settings_Inventory_Record_Model::getTableNameFromType($type);
		$this->assertFalse((new App\Db\Query())->from($tableName)->where(['id' => static::$id])->exists(), 'The record was not removed from the database ID: ' . static::$id);
	}
}
