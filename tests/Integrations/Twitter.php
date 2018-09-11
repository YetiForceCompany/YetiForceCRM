<?php
/**
 * Api integrations test class.
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
	private static $listId;

	private static function addField($moduleName)
	{
		$moduleModel = \Settings_LayoutEditor_Module_Model::getInstanceByName($moduleName);
		$block = $moduleModel->getBlocks()['LBL_CONTACT_INFORMATION'];
		$type = 'Twitter';
		$suffix = '_t' . (count(static::$twitterFields));
		$key = $type . $suffix;
		$param['fieldType'] = $type;
		$param['fieldLabel'] = $type . 'FL' . $suffix;
		$param['fieldName'] = strtolower($type . 'FL' . $suffix);
		$param['blockid'] = $block->id;
		$param['sourceModule'] = $moduleName;
		$param['fieldTypeList'] = 0;
		$moduleModel = \Settings_LayoutEditor_Module_Model::getInstanceByName($param['sourceModule']);
		static::$twitterFields[] = $moduleModel->addField($param['fieldType'], $block->id, $param);
	}

	/**
	 * @codeCoverageIgnore
	 * Setting of tests.
	 */
	public static function setUpBeforeClass()
	{
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
		$moduleModel = \Settings_LayoutEditor_Module_Model::getInstanceByName($param['sourceModule']);
		static::$twitterFields[] = $moduleModel->addField($param['fieldType'], $block->id, $param);

		/*$recordModel = \Vtiger_Record_Model::getCleanInstance('Contacts');
		$recordModel->set($param['fieldLabel'], 'yetiforceen');
		$recordModel->save();
		static::$listId[] = $recordModel->getId();*/

		/*static::addField('Contacts');

		$recordModel = \Vtiger_Record_Model::getCleanInstance('Contacts');
		$recordModel->set($param['fieldLabel'], $twitterLogin);
		$recordModel->save();*/
	}

	public function testAddTwitter()
	{
		$recordModel = \Vtiger_Record_Model::getCleanInstance('Contacts');
		$recordModel->set('assigned_user_id', \App\User::getActiveAdminId());
		$recordModel->set('lastname', 'Test');
		$recordModel->set(static::$twitterFields[0]->getFieldLabel(), 'yetiforceen');
		$recordModel->save();

		\App\DebugerEx::log($recordModel->getId(), static::$twitterFields[0]->getColumnName(), static::$twitterFields[0]->getTableName());
	}

	/*public function testMethodGetAllColumnName()
	{
		foreach (static::$listId as $recordId) {
			$recordModel = \Vtiger_Record_Model::getInstanceById($recordId);
			$arr = \Vtiger_SocialMedia_Model::getInstanceByRecordModel($recordModel)->getAllColumnName();
			\App\DebugerEx::log($arr);
		}
		//$this->assertSame($row['rolename'], 'Test');
	}*/

	/**
	 * @codeCoverageIgnore
	 * Cleaning after tests.
	 */
	public static function tearDownAfterClass()
	{
		foreach (static::$twitterFields as $fieldModel) {
			$fieldModel->delete();
		}
	}
}
