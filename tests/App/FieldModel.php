<?php
/**
 * FieldModel test class.
 *
 * @package   Tests
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
	 *
	 * @return \Vtiger_Field_Model
	 * @codeCoverageIgnore
	 */
	protected function createFieldModel(int $uiType, string $typeOfData, string $columnType)
	{
		$key = \md5($uiType . $typeOfData . $columnType);
		if (isset(self::$fields[$key])) {
			return self::$fields[$key];
		}
		$fieldInstance = new \vtlib\Field();
		$fieldInstance->name = 'custom_sender';
		$fieldInstance->table = 'vtiger_contactsubdetails';
		$fieldInstance->label = 'FL_LABEL';
		$fieldInstance->column = 'custom_sender';
		$fieldInstance->uitype = $uiType;
		$fieldInstance->typeofdata = $typeOfData;
		$fieldInstance->columntype = $columnType;
		return self::$fields[$key] = \Vtiger_Field_Model::getInstanceFromFieldObject($fieldInstance);
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
		$fieldModel = self::createFieldModel($uiType, $typeOfData, $columnType);
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
	public function testValidateUserFormatWrongData(int $uiType, string $typeOfData, string $columnType, $value)
	{
		$fieldModel = self::createFieldModel($uiType, $typeOfData, $columnType);
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
	public function testValidateWrongData(int $uiType, string $typeOfData, string $columnType, $value)
	{
		$fieldModel = self::createFieldModel($uiType, $typeOfData, $columnType);
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
		$fieldModel = self::createFieldModel($uiType, $typeOfData, $columnType);
		$fieldModel->name = 'val';
		$request = new \App\Request([], false);
		$request->set('val', $value);
		$this->assertNull($fieldModel->getUITypeModel()->setDefaultValueFromRequest($request));
		$this->assertSame($value, $fieldModel->get('defaultvalue'));
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
		$fieldModel = self::createFieldModel($uiType, $typeOfData, $columnType);
		$fieldModel->name = 'val';
		$request = new \App\Request([], false);
		$request->set('val', $value);
		$this->expectExceptionCode(406);
		$fieldModel->getUITypeModel()->setDefaultValueFromRequest($request);
	}

	/**
	 * Data provider for the test of correct data. For the "validate" method test.
	 *
	 * @return []
	 * @codeCoverageIgnore
	 */
	public function providerGoodData()
	{
		return [
			[5, 'D~O', 'varchar(255)', '2018-07-02'],
		];
	}

	/**
	 * Data provider for the test of wrong data. For the "validate" method test.
	 *
	 * @return []
	 * @codeCoverageIgnore
	 */
	public function providerWrongData()
	{
		return [
			[5, 'D~O', 'varchar(255)', '201872a'],
			[5, 'D~O', 'varchar(255)', '2018-72-12'],
			[5, 'D~O', 'varchar(255)', '-2018-02-12'],
			[5, 'D~O', 'varchar(255)', 20180612],
		];
	}

	/**
	 * Data provider for the test of correct data. For the "setDefaultValueFromRequest" method test.
	 *
	 * @return []
	 * @codeCoverageIgnore
	 */
	public function providerGoodDataForDefault()
	{
		return [
			[5, 'D~O', 'varchar(255)', '2018-07-02'],
			[5, 'D~O', 'varchar(255)', '$(date : now)$'],
			[5, 'D~O', 'varchar(255)', '$(date : tomorrow)$'],
			[5, 'D~O', 'varchar(255)', '$(date : yesterday)$'],
			[5, 'D~O', 'varchar(255)', '$(date : monday this week)$'],
			[5, 'D~O', 'varchar(255)', '$(date : monday next week)$'],
			[5, 'D~O', 'varchar(255)', '$(date : first day of this month)$'],
			[5, 'D~O', 'varchar(255)', '$(date : last day of this month)$'],
			[5, 'D~O', 'varchar(255)', '$(date : first day of next month)$'],
		];
	}

	/**
	 * Data provider for the test of wrong data. For the "setDefaultValueFromRequest" method test.
	 *
	 * @return []
	 * @codeCoverageIgnore
	 */
	public function providerWrongDataForDefault()
	{
		return [
			[5, 'D~O', 'varchar(255)', '201807o2'],
			[5, 'D~O', 'varchar(255)', 'abc'],
			[5, 'D~O', 'varchar(255)', 20180612],
			[5, 'D~O', 'varchar(255)', '(date : now)'],
			[5, 'D~O', 'varchar(255)', '$date : now$'],
			[5, 'D~O', 'varchar(255)', '$(date)$'],
		];
	}
}
