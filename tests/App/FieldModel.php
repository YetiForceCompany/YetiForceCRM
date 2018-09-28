<?php
/**
 * FieldModel test class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Arkadiusz Adach <a.adach@yetiforce.com>
 */

namespace Tests\App;

/**
 * Class FieldModel for test.
 *
 * @package   Tests
 */
class FieldModel extends \Tests\Base
{
	/**
	 * @var \Vtiger_Field_Model[]
	 */
	protected static $fields;

	/**
	 * Create Vtiger_Field_Model for tests.
	 *
	 * @param int    $uiType
	 * @param string $typeOfData
	 * @param string $columnType
	 * @param mixed  $value
	 *
	 * @return \Vtiger_Field_Model
	 * @codeCoverageIgnore
	 */
	protected function createFieldModel(int $uiType, string $typeOfData, string $columnType, $value)
	{
		$key = \md5($uiType . $typeOfData . $columnType);
		if (isset(static::$fields[$key])) {
			return static::$fields[$key];
		}
		$fieldInstance = new \vtlib\Field();
		$fieldInstance->name = 'custom_sender';
		$fieldInstance->table = 'vtiger_contactsubdetails';
		$fieldInstance->label = 'FL_LABEL';
		$fieldInstance->column = 'custom_sender';
		$fieldInstance->uitype = $uiType;
		$fieldInstance->typeofdata = $typeOfData;
		$fieldInstance->columntype = $columnType;
		return static::$fields[$key] = \Vtiger_Field_Model::getInstanceFromFieldObject($fieldInstance);
	}

	/**
	 * Test validate for good data.
	 *
	 * @param int    $uiType
	 * @param string $typeOfData
	 * @param string $columnType
	 * @param mixed  $value
	 * @dataProvider providerGoodData
	 */
	public function testValidateGoodData(int $uiType, string $typeOfData, string $columnType, $value)
	{
		$fieldModel = static::createFieldModel($uiType, $typeOfData, $columnType, $value);
		$this->assertNull($fieldModel->getUITypeModel()->validate($value, true));
		$this->assertNull($fieldModel->getUITypeModel()->validate($value, false));
	}

	/**
	 * Test validate for user format.
	 *
	 * @param int    $uiType
	 * @param string $typeOfData
	 * @param string $columnType
	 * @param mixed  $value
	 * @dataProvider providerWrongData
	 */
	public function testValidateUserFormatWrongeData(int $uiType, string $typeOfData, string $columnType, $value)
	{
		$fieldModel = static::createFieldModel($uiType, $typeOfData, $columnType, $value);
		$this->expectExceptionCode(406);
		$fieldModel->getUITypeModel()->validate($value, true);
	}

	/**
	 * Test validate for wrong data.
	 *
	 * @param int    $uiType
	 * @param string $typeOfData
	 * @param string $columnType
	 * @param mixed  $value
	 * @dataProvider providerWrongData
	 */
	public function testValidateWrongeData(int $uiType, string $typeOfData, string $columnType, $value)
	{
		$fieldModel = static::createFieldModel($uiType, $typeOfData, $columnType, $value);
		$this->expectExceptionCode(406);
		$fieldModel->getUITypeModel()->validate($value, false);
	}

	/**
	 * Test default value for good data.
	 *
	 * @param int    $uiType
	 * @param string $typeOfData
	 * @param string $columnType
	 * @param mixed  $value
	 *
	 * @throws \App\Exceptions\Security
	 *
	 * @dataProvider providerGoodDataForDefault
	 */
	public function testDefaultValueGoodData(int $uiType, string $typeOfData, string $columnType, $value)
	{
		$fieldModel = static::createFieldModel($uiType, $typeOfData, $columnType, $value);
		$fieldModel->name = 'val';
		$request = new \App\Request([]);
		$request->set('val', $value);
		$this->assertNull($fieldModel->getUITypeModel()->setDefaultValueFromRequest($request));
		$this->assertSame($fieldModel->getDBValue($value), $fieldModel->get('defaultvalue'));
	}

	/**
	 * Test default value for wrong data.
	 *
	 * @param int    $uiType
	 * @param string $typeOfData
	 * @param string $columnType
	 * @param mixed  $value
	 *
	 * @throws \App\Exceptions\Security
	 *
	 * @dataProvider providerWrongDataForDefault
	 */
	public function testDefaultValueWrongData(int $uiType, string $typeOfData, string $columnType, $value)
	{
		$fieldModel = static::createFieldModel($uiType, $typeOfData, $columnType, $value);
		$fieldModel->name = 'val';
		$request = new \App\Request([]);
		$request->set('val', $value);
		$this->expectExceptionCode(406);
		$fieldModel->getUITypeModel()->setDefaultValueFromRequest($request);
	}

	/**
	 * Data provider.
	 *
	 * @return []
	 * @codeCoverageIgnore
	 */
	public function providerGoodData()
	{
		return [
			[5, 'D~O', 'varchar(255)', '2018-07-02'], //date
		];
	}

	/**
	 * Data provider.
	 *
	 * @return []
	 * @codeCoverageIgnore
	 */
	public function providerWrongData()
	{
		return [
			[5, 'D~O', 'varchar(255)', '201872a'], //date
			[5, 'D~O', 'varchar(255)', '2018-72-12'], //date
			[5, 'D~O', 'varchar(255)', '-2018-02-12'], //date
			[5, 'D~O', 'varchar(255)', 20180612], //date
		];
	}

	/**
	 * Data provider.
	 *
	 * @return []
	 * @codeCoverageIgnore
	 */
	public function providerGoodDataForDefault()
	{
		return [
			[5, 'D~O', 'varchar(255)', '2018-07-02'], //date
			[5, 'D~O', 'varchar(255)', '$(date : now)$'], //date
			[5, 'D~O', 'varchar(255)', '$(date : tomorrow)$'], //date
			[5, 'D~O', 'varchar(255)', '$(date : yesterday)$'], //date
			[5, 'D~O', 'varchar(255)', '$(date : monday this week)$'], //date
			[5, 'D~O', 'varchar(255)', '$(date : monday next week)$'], //date
			[5, 'D~O', 'varchar(255)', '$(date : first day of this month)$'], //date
			[5, 'D~O', 'varchar(255)', '$(date : last day of this month)$'], //date
			[5, 'D~O', 'varchar(255)', '$(date : first day of next month)$'], //date
		];
	}

	/**
	 * Data provider.
	 *
	 * @return []
	 * @codeCoverageIgnore
	 */
	public function providerWrongDataForDefault()
	{
		return [
			[5, 'D~O', 'varchar(255)', '201807o2'], //date
			[5, 'D~O', 'varchar(255)', 'abc'], //date
			[5, 'D~O', 'varchar(255)', 20180612], //date
			[5, 'D~O', 'varchar(255)', '(date : now)'], //date
			[5, 'D~O', 'varchar(255)', '$date : now$'], //date
			[5, 'D~O', 'varchar(255)', '$(date)$'], //date
		];
	}
}
