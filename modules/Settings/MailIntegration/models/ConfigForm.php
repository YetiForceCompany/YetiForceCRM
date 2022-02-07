<?php
/**
 * Settings MailIntegration ConfigForm model file.
 *
 * @package   Module
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

/**
 * Settings MailIntegration ConfigForm model class.
 */
class Settings_MailIntegration_ConfigForm_Model
{
	/**
	 * Get fields for config form.
	 *
	 * @param string $moduleName
	 *
	 * @return Vtiger_Field_Model[]
	 */
	public static function getFields(string $moduleName): array
	{
		$config = App\Config::module('MailIntegration', null, []);
		$outlookUrls = $config['outlookUrls'] ?? [];
		$fields = [
			'modulesListQuickCreate' => [
				'isArray' => true,
				'purifyType' => 'Alnum',
				'uitype' => 33,
				'label' => 'LBL_QUICK_CREATE_MODULES',
				'labelDesc' => 'LBL_QUICK_CREATE_MODULES_DESC',
				'picklistValues' => \App\Module::getAllModuleNamesFilter(true, false, 0),
				'fieldvalue' => $config['modulesListQuickCreate'] ?? ''
			],
			'outlookUrls' => [
				'isArray' => true,
				'purifyType' => 'url',
				'uitype' => 33,
				'label' => 'LBL_OUTLOOK_ADDRESSES',
				'labelDesc' => 'LBL_OUTLOOK_ADDRESSES_DESC',
				'labelDescArgs' => 'https://outlook.live.com, https://outlook.office365.com, https://outlook.office.com',
				'picklistValues' => array_combine($outlookUrls, $outlookUrls),
				'fieldvalue' => $outlookUrls,
				'dataSelect' => 'tags',
			],
		];
		foreach ($fields as $key => $value) {
			$fields[$key] = \Vtiger_Field_Model::init($moduleName, $value, $key);
		}
		return $fields;
	}
}
