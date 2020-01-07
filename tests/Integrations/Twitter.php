<?php
/**
 * Twitter integrations test class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Arkadiusz Adach <a.adach@yetiforce.com>
 */

namespace Tests\Integrations;

/**
 * Class Twitter for test.
 *
 * @package   Tests
 */
class Twitter extends \Tests\Base
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
	 * @var int
	 */
	private static $idTwitter = 299792456;

	/**
	 * Add Twitter message.
	 *
	 * @param string $twitterLogin
	 *
	 * @throws \yii\db\Exception
	 * @codeCoverageIgnore
	 */
	private static function addTwitter(string $twitterLogin)
	{
		$db = \App\Db::getInstance()
			->createCommand()
			->insert('u_#__social_media_twitter', [
				'id_twitter' => static::$idTwitter++,
				'twitter_login' => $twitterLogin,
				'twitter_name' => $twitterLogin,
				'message' => 'TEST',
				'created' => \date('Y-m-d H:i:s'),
			])->execute();
	}

	/**
	 * @codeCoverageIgnore
	 * Setting of tests.
	 */
	public static function setUpBeforeClass(): void
	{
		\App\Config::set('component', 'Social', 'TWITTER_ENABLE_FOR_MODULES', ['Contacts']);
		$moduleModel = \Settings_LayoutEditor_Module_Model::getInstanceByName('Contacts');
		$block = $moduleModel->getBlocks()['LBL_CONTACT_INFORMATION'];
		$type = 'Twitter';
		$suffix = '_t1';
		$param['fieldType'] = $type;
		$param['fieldLabel'] = $type . 'FL' . $suffix;
		$param['fieldName'] = strtolower($type . 'FL' . $suffix);
		$param['blockid'] = $block->id;
		$param['sourceModule'] = 'Contacts';
		$param['fieldTypeList'] = 0;
		static::$twitterFields[] = $moduleModel->addField($param['fieldType'], $block->id, $param);
		static::addTwitter('yeti');
		static::addTwitter('yetiforceen');
		static::addTwitter('forceen');
	}

	/**
	 * Testing configuration for module.
	 */
	public function testConfigModule()
	{
		$this->assertInternalType(
			'array', \App\Config::component('Social', 'TWITTER_ENABLE_FOR_MODULES'),
			'Module Contacts not configured for social media'
		);
		$this->assertTrue(
			\in_array('Contacts', \App\Config::component('Social', 'TWITTER_ENABLE_FOR_MODULES')),
			'Module Contacts not configured for social media'
		);
	}

	/**
	 * Check if the Twitter field exists.
	 */
	public function testFieldTwitter()
	{
		$this->assertIsInt(static::$twitterFields[0]->getId());
		$this->assertTrue(
			(new \App\Db\Query())
				->from('vtiger_field')
				->where(['fieldid' => static::$twitterFields[0]->getId()])->exists(),
			'Field twitter not exists'
		);
		$fieldModel = \Vtiger_Module_Model::getInstance('Contacts')
			->getFieldByName(static::$twitterFields[0]->getFieldName());
		$this->assertNotFalse($fieldModel, 'Vtiger_Field_Model problem - not exists');
		$this->assertSame(
			static::$twitterFields[0]->getId(),
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
	public function testUiTypeWrongData($value)
	{
		$this->expectExceptionCode(406);
		static::$twitterFields[0]->getUITypeModel()->validate($value, false);
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
	public function testUiTypeUserFormatWrongData($value)
	{
		$this->expectExceptionCode(406);
		static::$twitterFields[0]->getUITypeModel()->validate($value, true);
	}

	/**
	 * Validation testing for uitype twitter.
	 *
	 * @param $value
	 *
	 * @throws \App\Exceptions\Security
	 * @dataProvider providerUiTypeGoodData
	 */
	public function testUiTypeGoodData($value)
	{
		$this->assertNull(static::$twitterFields[0]->getUITypeModel()->validate($value, false));
	}

	/**
	 * Data provider for testUiTypeWrongData.
	 *
	 * @return []
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
	 * @return []
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
	public function testAddTwitter()
	{
		$recordModel = \Vtiger_Record_Model::getCleanInstance('Contacts');
		$recordModel->set('assigned_user_id', \App\User::getActiveAdminId());
		$recordModel->set('lastname', 'Test');
		$recordModel->set(static::$twitterFields[0]->getColumnName(), 'yetiforceen');
		$recordModel->save();
		static::$listId[] = $recordModel->getId();

		$this->assertSame('yetiforceen',
			(new \App\Db\Query())->select([static::$twitterFields[0]->getColumnName()])
				->from(static::$twitterFields[0]->getTableName())
				->where(['contactid' => $recordModel->getId()])->scalar()
		);
		$this->assertTrue((new \App\Db\Query())
			->from('u_#__social_media_twitter')
			->where(['twitter_login' => 'yeti'])->exists(), 'Twitter message not exists');
		$this->assertTrue((new \App\Db\Query())
			->from('u_#__social_media_twitter')
			->where(['twitter_login' => 'forceen'])->exists(), 'Twitter message not exists');
	}

	/**
	 * Testing editing a Twitter account.
	 *
	 * @throws \Exception
	 */
	public function testEditTwitter()
	{
		$recordModel = \Vtiger_Record_Model::getInstanceById(static::$listId[0]);
		$recordModel->set(static::$twitterFields[0]->getColumnName(), 'yeti');
		$recordModel->save();
		$this->assertSame('yeti',
			(new \App\Db\Query())->select([static::$twitterFields[0]->getColumnName()])
				->from(static::$twitterFields[0]->getTableName())
				->where(['contactid' => $recordModel->getId()])->scalar()
		);
		$this->assertTrue((new \App\Db\Query())
			->from('u_#__social_media_twitter')
			->where(['twitter_login' => 'yeti'])->exists(), 'Twitter message not exists');
		$this->assertTrue((new \App\Db\Query())
			->from('u_#__social_media_twitter')
			->where(['twitter_login' => 'forceen'])->exists(), 'Twitter message not exists');
		$this->assertFalse((new \App\Db\Query())
			->from('u_#__social_media_twitter')
			->where(['twitter_login' => 'yetiforceen'])->exists(), 'Twitter message exists');
	}

	/**
	 * Testing editing a Twitter account, set empty value.
	 *
	 * @throws \Exception
	 */
	public function testEditTwitterEmpty()
	{
		$recordModel = \Vtiger_Record_Model::getInstanceById(static::$listId[0]);
		$recordModel->set(static::$twitterFields[0]->getColumnName(), '');
		$recordModel->save();
		$this->assertFalse((new \App\Db\Query())
			->from('u_#__social_media_twitter')
			->where(['twitter_login' => 'yeti'])->exists(), 'Twitter message exists');
		$this->assertTrue((new \App\Db\Query())
			->from('u_#__social_media_twitter')
			->where(['twitter_login' => 'forceen'])->exists(), 'Twitter message not exists');
	}

	/**
	 * Testing editing a Twitter account, set not empty value.
	 *
	 * @throws \Exception
	 */
	public function testEditTwitterNotEmpty()
	{
		$recordModel = \Vtiger_Record_Model::getInstanceById(static::$listId[0]);
		$recordModel->set(static::$twitterFields[0]->getColumnName(), 'forceen');
		$recordModel->save();
		$this->assertTrue((new \App\Db\Query())
			->from('u_#__social_media_twitter')
			->where(['twitter_login' => 'forceen'])->exists(), 'Twitter message not exists');
	}

	/**
	 * Removal testing.
	 *
	 * @throws \Exception
	 */
	public function testDeleteTwitter()
	{
		$recordModel = \Vtiger_Record_Model::getInstanceById(static::$listId[0]);
		$recordModel->delete();
		$this->assertFalse((new \App\Db\Query())
			->from('u_#__social_media_twitter')
			->where(['twitter_login' => 'forceen'])->exists(), 'Twitter message exists');
	}

	/**
	 * @codeCoverageIgnore
	 * Cleaning after tests.
	 */
	public static function tearDownAfterClass()
	{
		\App\Config::set('component', 'Social', 'TWITTER_ENABLE_FOR_MODULES', []);
		foreach (static::$twitterFields as $fieldModel) {
			$fieldModel->delete();
		}
	}
}
