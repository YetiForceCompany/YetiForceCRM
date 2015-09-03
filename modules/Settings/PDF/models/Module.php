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
	var $listFields = array('module_name' => 'Module', 'summary' => 'Summary', 'cola' => 'ColA', 'colb' => 'ColB', 'colc' => 'ColC', 'cold' => 'ColD');
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
		return "javascript:Settings_PDF_List_Js.triggerCreate('index.php?module=PDF&parent=Settings&view=Edit')";
	}

	public static function getCreateRecordUrl()
	{
		return 'index.php?module=PDF&parent=Settings&view=Edit';
	}

	public static function getSupportedModules()
	{
		$moduleModels = Vtiger_Module_Model::getAll(array(0, 2));
		$supportedModuleModels = array();
		foreach ($moduleModels as $tabId => $moduleModel) {
			if ($moduleModel->isWorkflowSupported()) {
				$supportedModuleModels[$tabId] = $moduleModel;
			}
		}
		return $supportedModuleModels;
	}

	public function getListFields()
	{
		if (!$this->listFieldModels) {
			$fields = $this->listFields;
			$fieldObjects = array();
			$fieldsNoSort = array('module_name', 'execution_condition', 'all_tasks', 'active_tasks');
			foreach ($fields as $fieldName => $fieldLabel) {
				if (in_array($fieldName, $fieldsNoSort)) {
					$fieldObjects[$fieldName] = new Vtiger_Base_Model(array('name' => $fieldName, 'label' => $fieldLabel, 'sort' => false));
				} else {
					$fieldObjects[$fieldName] = new Vtiger_Base_Model(array('name' => $fieldName, 'label' => $fieldLabel));
				}
			}
			$this->listFieldModels = $fieldObjects;
		}
		return $this->listFieldModels;
	}
}
