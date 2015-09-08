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
	var $listFields = ['module_name' => 'Module', 'summary' => 'Summary', 'cola' => 'ColA', 'colb' => 'ColB', 'colc' => 'ColC', 'cold' => 'ColD'];
	var $step1Fields = ['module_name', 'summary'];
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
			$supportedModuleModels[$tabId] = $moduleModel;
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
}
