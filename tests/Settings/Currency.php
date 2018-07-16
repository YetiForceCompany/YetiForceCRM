<?php

/**
 * Currency test class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Arkadiusz Sołek <a.solek@yetiforce.com>
 */
class Currency extends \Tests\Base
{
	/**
	 * Currency id.
	 */
	private static $id;

	/**
	 * Testing add currency creation.
	 */
	public function testAddCurrency()
	{
		$recordModel = new Settings_Currency_Record_Model();
		$recordModel->set('currency_name', 'Bahrain');
		$recordModel->set('conversion_rate', 1.65);
		$recordModel->set('currency_status', 'Active');
		$recordModel->set('currency_code', 'BHD');
		$recordModel->set('currency_symbol', 'BD');
		static::$id = $recordModel->save();
		$this->assertNotNull(static::$id, 'Id is null');

		$row = (new \App\Db\Query())->from('vtiger_currency_info')->where(['id' => static::$id])->one();
		$this->assertNotFalse($row, 'No record id: ' . static::$id);
		$this->assertSame($row['currency_name'], 'Bahrain');
		$this->assertSame((float) $row['conversion_rate'], 1.65);
		$this->assertSame($row['currency_status'], 'Active');
		$this->assertSame($row['currency_code'], 'BHD');
		$this->assertSame($row['currency_symbol'], 'BD');
	}

	/**
	 * Testing edit currency creation.
	 */
	public function testEditCurrency()
	{
		$recordModel = Settings_Currency_Record_Model::getInstance(static::$id);
		$recordModel->set('currency_name', 'Argentina');
		$recordModel->set('conversion_rate', 0.65);
		$recordModel->set('currency_status', 'No');
		$recordModel->set('currency_code', 'ARS');
		$recordModel->set('currency_symbol', '$');
		static::$id = $recordModel->save();

		$row = (new \App\Db\Query())->from('vtiger_currency_info')->where(['id' => static::$id])->one();
		$this->assertNotFalse($row, 'No record id: ' . static::$id);
		$this->assertSame($row['currency_name'], 'Argentina');
		$this->assertSame((float) $row['conversion_rate'], 0.65);
		$this->assertSame($row['currency_status'], 'No');
		$this->assertSame($row['currency_code'], 'ARS');
		$this->assertSame($row['currency_symbol'], '$');
	}

	/**
	 * Testing ListView model functions.
	 */
	public function testListViewModel()
	{
		$model = \Settings_Currency_ListView_Model::getInstance();
		$this->assertInstanceOf('App\Db\Query', $model->getBasicListQuery(), 'Query object expected.');
	}

	/**
	 * Testing Module model functions.
	 */
	public function testModuleModel()
	{
		$model = \Settings_Currency_Module_Model::getInstance('Settings:Currency');
		$this->assertFalse($model->isPagingSupported(), 'Expected paging supported flag is false');
		$this->assertNotEmpty($model->getCreateRecordUrl(), 'Expected create record url is not empty');
		$this->assertNotEmpty($model->getBaseTable(), 'Expected base table is not empty');
	}

	/**
	 * Testing Record model functions.
	 */
	public function testRecordModel()
	{
		$recordModel = \Settings_Currency_Record_Model::getInstance(static::$id);
		$this->assertNotNull($recordModel, 'Expected recordModel is not empty');
		$this->assertNotEmpty($recordModel->getName(), 'Expected name is not empty');
		$this->assertNotEmpty($recordModel->getName(), 'Expected name is not empty');
		$this->assertFalse($recordModel->isBaseCurrency(), 'Expected that record is not base currency');
		$this->assertInternalType('array', $recordModel->getRecordLinks(), 'Expected that record links is always array type');
		$this->assertSame($recordModel->getDeleteStatus(), 0, 'Expected that delete status of record is 0');
		$this->assertNotNull(\Settings_Currency_Record_Model::getInstance($recordModel->getName()), 'Expected record model instance.');
		$allRecords = \Settings_Currency_Record_Model::getAll();
		$this->assertInternalType('array', $allRecords, 'Expected that all records result is always array type');
		$this->assertNotEmpty($allRecords, 'Expected that all records result is not empty');
		$allNonmappedRecords = \Settings_Currency_Record_Model::getAllNonMapped();
		$this->assertInternalType('array', $allNonmappedRecords, 'Expected that all non mapped records result is always array type');
	}

	/**
	 * Testing deletet currency creation.
	 */
	public function testDeletetCurrency()
	{
		Settings_Currency_Module_Model::delete(static::$id);
		$this->assertTrue((new \App\Db\Query())->from('vtiger_currency_info')->where(['and', ['id' => static::$id, 'deleted' => 1]])->exists());
		$this->assertTrue((new \App\Db\Query())->from('vtiger_currency_info')->where(['and', ['currency_name' => 'Argentina', 'deleted' => 1]])->exists());
	}
}
