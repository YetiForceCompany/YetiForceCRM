<?php
/**
 * Module Class for PDF Settings
 * @package YetiForce.Model
 * @license licenses/License.html
 * @author Maciej Stencel <m.stencel@yetiforce.com>
 */

class Settings_PDF_Module_Model extends Settings_Vtiger_Module_Model
{

	var $baseTable = 'a_yf_pdf';
	var $baseIndex = 'pdfid';
	var $listFields = [
		'module_name' => 'Module',
		'status' => 'LBL_STATUS',
		'primary_name' => 'LBL_PRIMARY_NAME',
		'secondary_name' => 'LBL_SECONDARY_NAME',
		'set_author' => 'LBL_SET_AUTHOR',
		'set_creator' => 'LBL_SET_CREATOR',
		'set_keywords' => 'LBL_SET_KEYWORDS',
		'cola' => 'ColA',
		'colb' => 'ColB',
		'colc' => 'ColC',
		'cold' => 'ColD'
	];
	public static $allFields = [
		'module_name',
		'status',
		'primary_name',
		'secondary_name',
		'set_author',
		'set_creator',
		'set_keywords',
		'metatags_status',
		'set_subject',
		'set_title',
		'cola',
		'colb',
		'colc',
		'cold',
		'cole',
		'colf',
		'colg'
	];
	public static $step1Fields = ['status', 'primary_name', 'secondary_name', 'module_name', 'metatags_status', 'set_subject', 'set_title', 'set_author', 'set_creator', 'set_keywords'];
	public static $step2Fields = ['cola'];
	public static $step3Fields = ['colb'];
	public static $step4Fields = ['colc'];
	public static $step5Fields = ['cold'];
	public static $step6Fields = ['cole'];
	public static $step7Fields = ['colf'];
	public static $step8Fields = ['colg'];
	var $name = 'PDF';

	/**
	 * Function to get the url for default view of the module
	 * @return <string> - url
	 */
	public static function getDefaultUrl()
	{
		return 'index.php?module=PDF&parent=Settings&view=List';
	}

	/**
	 * Function to get the url for create view of the module
	 * @return <string> - url
	 */
	public static function getCreateViewUrl()
	{
		return "javascript:Settings_PDF_List_Js.triggerCreate('".self::getCreateRecordUrl()."')";
	}

	public static function getCreateRecordUrl()
	{
		return 'index.php?module=PDF&parent=Settings&view=Edit';
	}

	public static function getSupportedModules()
	{
		$moduleModels = Vtiger_Module_Model::getAll([0, 2]);
		$supportedModuleModels = [];
		foreach ($moduleModels as $tabId => $moduleModel) {
			if ($moduleModel->isEntityModule()) {
				$supportedModuleModels[$tabId] = $moduleModel;
			}
		}
		return $supportedModuleModels;
	}

	public function getListFields()
	{
		if (!$this->listFieldModels) {
			$fields = $this->listFields;
			$fieldObjects = [];
			$fieldsNoSort = ['module_name'];
			foreach ($fields as $fieldName => $fieldLabel) {
				if (in_array($fieldName, $fieldsNoSort)) {
					$fieldObjects[$fieldName] = new Vtiger_Base_Model(['name' => $fieldName, 'label' => $fieldLabel, 'sort' => false]);
				} else {
					$fieldObjects[$fieldName] = new Vtiger_Base_Model(['name' => $fieldName, 'label' => $fieldLabel]);
				}
			}
			$this->listFieldModels = $fieldObjects;
		}
		return $this->listFieldModels;
	}

	public static function getFieldsByStep($step=1) {
		switch($step) {
			case 8:
				return self::$step8Fields;
			case 7:
				return self::$step7Fields;
			case 6:
				return self::$step6Fields;
			case 5:
				return self::$step5Fields;
			case 4:
				return self::$step4Fields;
			case 3:
				return self::$step3Fields;
			case 2:
				return self::$step2Fields;
			case 1:
			default:
				return self::$step1Fields;
		}
	}
}
