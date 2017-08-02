<?php

/**
 * OSSMailScanner module model class
 * @package YetiForce.Model
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 2.0 (licenses/License.html or yetiforce.com)
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

	public function getSettingLinks()
	{
		Vtiger_Loader::includeOnce('~~modules/com_vtiger_workflow/VTWorkflowUtils.php');
		$layoutEditorImagePath = Vtiger_Theme::getImagePath('LayoutEditor.gif');
		$settingsLinks = [];
		$db = PearDatabase::getInstance();
		$result = $db->query("SELECT fieldid FROM vtiger_settings_field WHERE name = 'OSSMailScanner' AND description = 'OSSMailScanner'");

		$settingsLinks[] = [
			'linktype' => 'LISTVIEWSETTING',
			'linklabel' => 'LBL_MODULE_CONFIGURATION',
			'linkurl' => 'index.php?module=OSSMailScanner&parent=Settings&view=Index&block=4&fieldid=' . $db->getSingleValue($result),
			'linkicon' => $layoutEditorImagePath
		];
		return $settingsLinks;
	}
}
