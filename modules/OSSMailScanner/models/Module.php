<?php

/**
 * OSSMailScanner module model class.
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
class OSSMailScanner_Module_Model extends Vtiger_Module_Model
{
	public $actionsDir = false;

	public function __construct()
	{
		$this->actionsDir = ROOT_DIRECTORY . '/modules/OSSMailScanner/scanneractions';
	}

	public function getDefaultViewName()
	{
		return 'index';
	}

	/** {@inheritdoc} */
	public function getSettingLinks(): array
	{
		Vtiger_Loader::includeOnce('~~modules/com_vtiger_workflow/VTWorkflowUtils.php');
		$settingsLinks = [];
		if ($menu = Settings_Vtiger_MenuItem_Model::getInstance('Mail Scanner')) {
			$settingsLinks[] = [
				'linktype' => 'LISTVIEWSETTING',
				'linklabel' => 'LBL_MODULE_CONFIGURATION',
				'linkurl' => 'index.php?module=OSSMailScanner&parent=Settings&view=Index&block=' . $menu->get('blockid') . '&fieldid=' . $menu->get('fieldid'),
				'linkicon' => 'adminIcon-mail-scanner',
			];
		}
		return $settingsLinks;
	}
}
