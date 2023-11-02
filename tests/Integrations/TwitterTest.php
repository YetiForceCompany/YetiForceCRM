<?php
/**
 * Twitter integrations test class.
 *
 * @package   Tests
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 6.5 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Arkadiusz Adach <a.adach@yetiforce.com>
 */

namespace Tests\Integrations;

/**
 * Class Twitter for test.
 *
 * @package   Tests
 *
 * @internal
 * @coversNothing
 */
final class TwitterTest extends \Tests\Base
{
	/**
	 * @var \Settings_LayoutEditor_Field_Model[]
	 */
	private static $twitterFields;
	/**
	 * @var int[]
	 */
	private static $listId;

	/**
	 * @codeCoverageIgnore
	 * Setting of tests.
	 */
	public static function setUpBeforeClass(): void
	{
		$moduleModel = \Settings_LayoutEditor_Module_Model::getInstance('Settings:LayoutEditor')->setSourceModule('Contacts');
		$block = $moduleModel->getBlocks()['LBL_CONTACT_INFORMATION'];
		$type = 'Twitter';
		$suffix = '_t1';
		$param['fieldType'] = $type;
		$param['fieldLabel'] = $type . 'FL' . $suffix;
		$param['fieldName'] = strtolower($type . 'FL' . $suffix);
		$param['blockid'] = $block->id;
		$param['sourceModule'] = 'Contacts';
		$param['fieldTypeList'] = 0;
		self::$twitterFields[] = $moduleModel->addField($param['fieldType'], $block->id, $param);
	}

	/**
	 * Check if the Twitter field exists.
	 */
	public function testFieldTwitter(): void
	{
		static::assertIsInt(self::$twitterFields[0]->getId());
		static::assertTrue(
			(new \App\Db\Query())->from('vtiger_field')->where(['fieldid' => self::$twitterFields[0]->getId()])->exists(),
			'Field twitter not exists'
		);
		$fieldModel = \Vtiger_Module_Model::getInstance('Contacts')->getFieldByName(self::$twitterFields[0]->getName());
		static::assertNotFalse($fieldModel, 'Vtiger_Field_Model problem - not exists');
		static::assertSame(
			self::$twitterFields[0]->getId(),
			$fieldModel->getId(),
			'Vtiger_Field_Model problem'
		);
	}

	/**
	 * Validation testing for uitype twitter.
	 *
	 * @param mixed $value
	 *
	 * @throws \App\Exceptions\Security
	 *
	 * @dataProvider providerUiTypeWrongData
	 */
	public function testUiTypeWrongData($value): void
	{
		$this->expectExceptionCode(406);
		self::$twitterFields[0]->getUITypeModel()->validate($value, false);
	}

	/**
	 * Validation testing for uitype twitter - user format.
	 *
	 * @param mixed $value
	 *
	 * @throws \App\Exceptions\Security
	 *
	 * @dataProvider providerUiTypeWrongData
	 */
	public function testUiTypeUserFormatWrongData($value): void
	{
		$this->expectExceptionCode(406);
		self::$twitterFields[0]->getUITypeModel()->validate($value, true);
	}

	/**
	 * Validation testing for uitype twitter.
	 *
	 * @param $value
	 *
	 * @throws \App\Exceptions\Security
	 * @dataProvider providerUiTypeGoodData
	 */
	public function testUiTypeGoodData($value): void
	{
		static::assertNull(self::$twitterFields[0]->getUITypeModel()->validate($value, false));
	}

	/**
	 * Data provider for testUiTypeWrongData.
	 *
	 * @return array
	 * @codeCoverageIgnore
	 */
	public function providerUiTypeWrongData()
	{
		return [
			['$#@%^$^%'],
			['gfdsgf abc'],
			['abcde1234567890abcde'],
		];
	}

	/**
	 * Data provider for testUiTypeGoodData.
	 *
	 * @return array
	 * @codeCoverageIgnore
	 */
	public function providerUiTypeGoodData()
	{
		return [
			['abc'],
			['yf123'],
			['YFlogin'],
		];
	}

	/**
	 * Testing adding a Twitter account.
	 *
	 * @throws \Exception
	 */
	public function testAddTwitter(): void
	{
		$recordModel = \Vtiger_Record_Model::getCleanInstance('Contacts');
		$recordModel->set('assigned_user_id', \App\User::getActiveAdminId());
		$recordModel->set('lastname', 'Test');
		$recordModel->set(self::$twitterFields[0]->getColumnName(), 'yetiforceen');
		$recordModel->save();
		self::$listId[] = $recordModel->getId();

		static::assertSame('yetiforceen',
			(new \App\Db\Query())->select([self::$twitterFields[0]->getColumnName()])
				->from(self::$twitterFields[0]->getTableName())
				->where(['contactid' => $recordModel->getId()])->scalar()
		);
	}

	/**
	 * Testing editing a Twitter account.
	 *
	 * @throws \Exception
	 */
	public function testEditTwitter(): void
	{
		$recordModel = \Vtiger_Record_Model::getInstanceById(self::$listId[0]);
		$recordModel->set(self::$twitterFields[0]->getColumnName(), 'yeti');
		$recordModel->save();
		static::assertSame('yeti',
			(new \App\Db\Query())->select([self::$twitterFields[0]->getColumnName()])
				->from(self::$twitterFields[0]->getTableName())
				->where(['contactid' => $recordModel->getId()])->scalar()
		);
	}

	/**
	 * Cleaning after tests.
	 */
	public static function tearDownAfterClass(): void
	{
		$recordModel = \Vtiger_Record_Model::getInstanceById(self::$listId[0]);
		$recordModel->delete();
		foreach (self::$twitterFields as $fieldModel) {
			$fieldModel->delete();
		}
		\App\Cache::clearAll();
	}
}
