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
	public static function setUpBeforeClass()
	{
		\var_dump(\App\User::getActiveAdminId());
		\App\User::setCurrentUserId(\App\User::getActiveAdminId());
		\var_dump(\App\User::getCurrentUserId());

		\AppConfig::set('modules', 'Contacts', ['enable_social' => ['twitter']]);
		$moduleModel = \Settings_LayoutEditor_Module_Model::getInstanceByName('Contacts');

		$block = $moduleModel->getBlocks()['LBL_CONTACT_INFORMATION'];
		$type = 'Twitter';
		$suffix = '_t1';
		$key = $type . $suffix;
		$param['fieldType'] = $type;
		$param['fieldLabel'] = $type . 'FL' . $suffix;
		$param['fieldName'] = strtolower($type . 'FL' . $suffix);
		$param['blockid'] = $block->id;
		$param['sourceModule'] = 'Contacts';
		$param['fieldTypeList'] = 0;

		$blockInstance = \vtlib\Block::getInstance('LBL_CONTACT_INFORMATION', 'Contacts');
		$fieldInstance = new \vtlib\Field();
		$fieldInstance->name = $param['fieldName'];
		$fieldInstance->label = $param['fieldLabel'];
		$fieldInstance->table = 'vtiger_contactdetails';
		$fieldInstance->column = $param['fieldName'];
		$fieldInstance->uitype = 313;
		$fieldInstance->typeofdata = 'V~O';
		$fieldInstance->displaytype = 1;
		$fieldInstance->quickcreate = 3;
		$fieldInstance->masseditable = 0;
		$blockInstance->addField($fieldInstance);

		\var_dump($fieldInstance->id);
		static::$twitterFields[] = \Settings_LayoutEditor_Field_Model::getInstance($fieldInstance->id);

		//static::$twitterFields[] =

		//static::$twitterFields[] = $moduleModel->addField($param['fieldType'], $block->id, $param);
		\App\Cache::clear();

		static::addTwitter('yeti');
		static::addTwitter('yetiforceen');
		static::addTwitter('forceen');
		\App\Cache::delete('getFieldsPermissions' . \App\User::getCurrentUserId(), $moduleModel->getId());
		\Vtiger_Cache::flush();
		\App\Cache::init();
		\App\Cache::staticClear();
	}

	/**
	 * Check if the Twitter field exists.
	 */
	public function testFieldTwitter()
	{
		$this->assertInternalType('integer', static::$twitterFields[0]->getId());
		$this->assertTrue(
			(new \App\Db\Query())
				->from('vtiger_field')
				->where(['fieldid' => static::$twitterFields[0]->getId()])->exists(),
			'Field twitter not exists'
		);

		\var_dump(static::$twitterFields[0]->getFieldName());

		$fm = \Vtiger_Field_Model::getInstance(static::$twitterFields[0]->getFieldName());
		//\var_dump('getFieldName', $fm);

		//\var_dump('cache', \Vtiger_Cache::$cacheEnable);

		$obj = new class() extends \Vtiger_Module_Model {
			public static function getInstance($mixed)
			{
				$inst = parent::getInstance($mixed);
				\var_dump('fields', $inst->fields);
				return $inst;
				//self::getInstanceFromModuleObject($moduleObject);
			}

			public function clearFields()
			{
				$this->fields = false;
			}

			public function getFieldByName($fieldName)
			{
				\var_dump($fieldName, $this->fields);
				return parent::getFieldByName($fieldName);
			}
		};
		//$obj::getInstance('Contacts');

		/*$recordModel = \Vtiger_Record_Model::getCleanInstance('Contacts');
		$res = \Vtiger_Module_Model::getInstance('Contacts')->getFieldsForSave($recordModel);
		\var_dump($res);*/

		//\Vtiger_Cache::getInstance()->set

		//\Vtiger_Cache::getInstance()->

		$fieldModel = $obj::getInstance('Contacts')->getFieldByName(static::$twitterFields[0]->getFieldName());
		\var_dump($fieldModel);
		/*$fieldModel = \Vtiger_Module_Model::getInstance('Contacts')
			->getFieldByName(static::$twitterFields[0]->getFieldName());*/

		$this->assertNotFalse($fieldModel, 'Vtiger_Field_Model problem - not exists');
		$this->assertSame(
			static::$twitterFields[0]->getId(),
			$fieldModel->getId(),
			'Vtiger_Field_Model problem'
		);
	}

	/**
	 * Testing configuration for module.
	 */
	public function testConfigModule()
	{
		$this->assertTrue(\is_array(\AppConfig::module('Contacts', 'enable_social')), 'Module Contacts not configured for social media');
		$this->assertTrue(\in_array('twitter', \AppConfig::module('Contacts', 'enable_social')), 'Module Contacts not configured for social media');
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
		\App\Cache::clear();
		$this->assertInternalType('integer', $recordModel->getId());
		static::$listId[] = $recordModel->getId();

		$this->assertSame('yetiforceen',
			(new \App\Db\Query())->select([static::$twitterFields[0]->getColumnName()])
				->from(static::$twitterFields[0]->getTableName())
				->where(['contactid' => $recordModel->getId()])->scalar(),
			"Bad value in table '" . static::$twitterFields[0]->getTableName() . "' column '" . static::$twitterFields[0]->getColumnName() . "'."
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
		\AppConfig::set('modules', 'Contacts', []);
		foreach (static::$twitterFields as $fieldModel) {
			$fieldModel->delete();
		}
	}
}
