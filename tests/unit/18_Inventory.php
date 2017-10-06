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
	 * save to database
	 */
	private function save($id, $type, $name, $value, $status)
	{
		if (empty($id)) {
			$recordModel = new Settings_Inventory_Record_Model();
		} else {
			$recordModel = Settings_Inventory_Record_Model::getInstanceById($id, $type);
		}

		if ($type === 'Discounts') {
			$recordModel->set('value', CurrencyField::convertToDBFormat($recordModel->get('value')));
		}

		$recordModel->set('id', $id);
		$recordModel->set('name', $name);
		$recordModel->set('value', $value);
		$recordModel->set('status', $status);
		$recordModel->setType($type);
		return $recordModel->save();
	}

	/**
	 * Testing inventory creation
	 */
	public function testAddInventory()
	{
		$type = 'Taxes';
		$name = 'testowy podatek';
		$value = 3.14;
		$status = 0;
		static::$id = $this->save('', $type, $name, $value, $status);
		$this->assertNotNull(static::$id, 'Id is null');

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

	/**
	 * Testing discount creation
	 */
	public function testAddDiscount()
	{
		$type = 'Discounts';
		$name = 'test name';
		$value = 3.14;
		$status = 0;
		static::$id = $this->save('', $type, $name, $value, $status);
		$this->assertNotNull(static::$id, 'Id is null');

		$tableName = Settings_Inventory_Record_Model::getTableNameFromType($type);
		$row = (new \App\Db\Query())->from($tableName)->where(['id' => static::$id])->one();
		$this->assertNotFalse($row, 'No record id: ' . static::$id);
		$this->assertEquals($row['name'], $name);
		$this->assertEquals($row['value'], $value);
		$this->assertEquals($row['status'], $status);
	}

	/**
	 * Testing discount edition
	 */
	public function testEditDiscount()
	{
		$type = 'Discounts';
		$name = 'test name edit';
		$value = 2.62;
		$status = 1;
		static::$id = $this->save('', $type, $name, $value, $status);
		$this->save('', $type, $name, $value, $status);

		$tableName = Settings_Inventory_Record_Model::getTableNameFromType($type);
		$row = (new \App\Db\Query())->from($tableName)->where(['id' => static::$id])->one();
		$this->assertNotFalse($row, 'No record id: ' . static::$id);
		$this->assertEquals($row['name'], $name);
		$this->assertEquals($row['value'], $value);
		$this->assertEquals($row['status'], $status);
	}

	/**
	 * Testing discount deletion
	 */
	public function testDeleteDiscount()
	{
		$type = 'Discounts';
		$recordModel = Settings_Inventory_Record_Model::getInstanceById(static::$id, $type);
		$recordModel->delete();

		$tableName = Settings_Inventory_Record_Model::getTableNameFromType($type);
		$this->assertFalse((new App\Db\Query())->from($tableName)->where(['id' => static::$id])->exists(), 'The record was not removed from the database ID: ' . static::$id);
	}
}
