<?php
/**
 * ApiAddress model class
 * @package YetiForce.CRMEntity
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 */

/**
 * Class ApiAddress
 */
class ApiAddress
{

	/**
	 * Invoked when special actions are performed on the module.
	 * @param String Module name
	 * @param String Event Type
	 */
	public function moduleHandler($moduleName, $eventType)
	{
		require_once('include/utils/utils.php');
		$registerLink = false;
		if ($eventType === 'module.postinstall') {
			//Add Assets Module to Customer Portal
			$registerLink = true;
			\App\Db::getInstance()->createCommand()->update('vtiger_tab', ['customized' => 0], ['name' => $moduleName])->execute();
			\App\Db::getInstance()->createCommand()->insert('vtiger_apiaddress', ['nominatim' => 0, 'key' => 0, 'source' => 'https://api.opencagedata.com/geocode/v1/', 'min_length' => 3])->execute();
		}
		$displayLabel = 'LBL_API_ADDRESS';
		if ($registerLink) {
			Settings_Vtiger_Module_Model::addSettingsField('LBL_INTEGRATION', [
				'name' => $displayLabel,
				'iconpath' => '',
				'description' => 'LBL_API_ADDRESS_DESCRIPTION',
				'linkto' => 'index.php?module=ApiAddress&parent=Settings&view=Configuration'
			]);
		} else {
			Settings_Vtiger_Module_Model::deleteSettingsField('LBL_INTEGRATION', $displayLabel);
		}
	}
}
