<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

class Settings_Vtiger_ConfigModule_Model extends Settings_Vtiger_Module_Model
{

	public $fileName = 'config/config.inc.php';
	public $completeData;
	public $data;

	/**
	 * Function to read config file
	 * @return <Array> The data of config file
	 */
	public function readFile()
	{
		if (!$this->completeData) {
			$this->completeData = file_get_contents($this->fileName);
		}
		return $this->completeData;
	}

	/**
	 * Function to get CompanyDetails Menu item
	 * @return menu item Model
	 */
	public function getMenuItem()
	{
		$menuItem = Settings_Vtiger_MenuItem_Model::getInstance('LBL_CONFIG_EDITOR');
		return $menuItem;
	}

	/**
	 * Function to get Edit view Url
	 * @return string Url
	 */
	public function getEditViewUrl()
	{
		$menuItem = $this->getMenuItem();
		return '?module=Vtiger&parent=Settings&view=ConfigEditorEdit&block=' . $menuItem->get('blockid') . '&fieldid=' . $menuItem->get('fieldid');
	}

	/**
	 * Function to get Detail view Url
	 * @return string Url
	 */
	public function getDetailViewUrl()
	{
		$menuItem = $this->getMenuItem();
		return '?module=Vtiger&parent=Settings&view=ConfigEditorDetail&block=' . $menuItem->get('blockid') . '&fieldid=' . $menuItem->get('fieldid');
	}

	/**
	 * Function to get Viewable data of config details
	 * @return <Array>
	 */
	public function getViewableData()
	{
		if (!$this->getData()) {
			$fileContent = $this->readFile();
			$pattern = '/\$([^=]+)=([^;]+);/';
			$matches = null;
			$matchesFound = preg_match_all($pattern, $fileContent, $matches);
			$configContents = array();
			if ($matchesFound) {
				$configContents = $matches[0];
			}
			$data = [];
			$editableFileds = $this->getEditableFields();
			foreach ($editableFileds as $fieldName => $fieldDetails) {
				foreach ($configContents as $configContent) {
					if (strpos($configContent, $fieldName)) {
						$fieldValue = explode(' = ', $configContent);
						$fieldValue = $fieldValue[1];
						if ($fieldName === 'upload_maxsize') {
							$fieldValue = round(number_format($fieldValue / 1048576, 2));
						}

						$data[$fieldName] = str_replace(";", '', str_replace("'", '', $fieldValue));
						break;
					}
				}
			}
			$this->setData($data);
		}
		return $this->getData();
	}

	/**
	 * Function to get picklist values
	 * @param string $fieldName
	 * @return array list of module names
	 */
	public function getPicklistValues($fieldName)
	{
		if ($fieldName === 'default_module') {
			$db = PearDatabase::getInstance();

			$presence = [0];
			$restrictedModules = array('Integration', 'Dashboard');
			$query = 'SELECT name, tablabel FROM vtiger_tab WHERE presence IN (%s) AND isentitytype = ? AND name NOT IN (%s)';
			$query = sprintf($query, generateQuestionMarks($presence), generateQuestionMarks($restrictedModules));
			$result = $db->pquery($query, [$presence, '1', $restrictedModules]);
			$numOfRows = $db->num_rows($result);

			$moduleData = array('Home' => 'Home');
			for ($i = 0; $i < $numOfRows; $i++) {
				$row = $db->query_result_rowdata($result, $i);
				$moduleData[$db->query_result($result, $i, 'name')] = $db->query_result($result, $i, 'tablabel');
			}
			return $moduleData;
		} else if ($fieldName === 'defaultLayout') {
			return Yeti_Layout::getAllLayouts();
		}
		return ['true', 'false'];
	}

	/**
	 * Function to get editable fields
	 * @return <Array> list of field names
	 */
	public function getEditableFields()
	{
		return array(
			'upload_maxsize' => array('label' => 'LBL_MAX_UPLOAD_SIZE', 'fieldType' => 'input'),
			'default_module' => array('label' => 'LBL_DEFAULT_MODULE', 'fieldType' => 'picklist'),
			'listview_max_textlength' => array('label' => 'LBL_MAX_TEXT_LENGTH_IN_LISTVIEW', 'fieldType' => 'input'),
			'list_max_entries_per_page' => array('label' => 'LBL_MAX_ENTRIES_PER_PAGE_IN_LISTVIEW', 'fieldType' => 'input'),
			'defaultLayout' => array('label' => 'LBL_DEFAULT_LAYOUT', 'fieldType' => 'picklist'),
			'breadcrumbs' => ['label' => 'LBL_SHOWING_BREADCRUMBS', 'fieldType' => 'checkbox'],
			'title_max_length ' => ['label' => 'LBL_TITLE_MAX_LENGHT', 'fieldType' => 'input'],
			'MINIMUM_CRON_FREQUENCY' => ['label' => 'LBL_MINIMUM_CRON_FREQUENCY', 'fieldType' => 'input'],
			'listMaxEntriesMassEdit' => ['label' => 'LBL_LIST_MAX_ENTRIES_MASSEDIT', 'fieldType' => 'input'],
			'backgroundClosingModal' => ['label' => 'LBL_BG_CLOSING_MODAL', 'fieldType' => 'checkbox'],
			'href_max_length' => ['label' => 'LBL_HREF_MAX_LEGTH', 'fieldType' => 'input'],
			'langInLoginView' => ['label' => 'LBL_SHOW_LANG_IN_LOGIN_PAGE', 'fieldType' => 'checkbox'],
			'layoutInLoginView' => ['label' => 'LBL_SHOW_LAYOUT_IN_LOGIN_PAGE', 'fieldType' => 'checkbox'],
		);
	}

	/**
	 * Function to save the data
	 */
	public function save()
	{
		$fileContent = $this->completeData;
		$updatedFields = $this->get('updatedFields');
		$validationInfo = $this->validateFieldValues($updatedFields);
		if ($validationInfo === true) {
			foreach ($updatedFields as $fieldName => $fieldValue) {
				$patternString = "\$%s = '%s';";
				if ($fieldName === 'upload_maxsize') {
					$fieldValue = $fieldValue * 1048576; //(1024 * 1024)
					$patternString = "\$%s = %s;";
				}
				if (in_array($fieldName, ['layoutInLoginView', 'langInLoginView'])) {
					$patternString = "\$%s = %s;";
				}
				$pattern = '/\$' . $fieldName . '[\s]+=([^;]+);/';
				$replacement = sprintf($patternString, $fieldName, ltrim($fieldValue, '0'));
				$fileContent = preg_replace($pattern, $replacement, $fileContent);
			}
			$filePointer = fopen($this->fileName, 'w');
			fwrite($filePointer, $fileContent);
			fclose($filePointer);
		}
		return $validationInfo;
	}

	/**
	 * Function to validate the field values
	 * @param <Array> $updatedFields
	 * @return string True/Error message
	 */
	public function validateFieldValues($updatedFields)
	{
		if (!preg_match('/[a-zA-z0-9]/', $updatedFields['default_module'])) {
			return 'LBL_INVALID_MODULE';
		} else if (!filter_var(ltrim($updatedFields['upload_maxsize'], '0'), FILTER_VALIDATE_INT) || !filter_var(ltrim($updatedFields['list_max_entries_per_page'], '0'), FILTER_VALIDATE_INT) || !filter_var(ltrim($updatedFields['listview_max_textlength'], '0'), FILTER_VALIDATE_INT)) {
			return 'LBL_INVALID_NUMBER';
		}
		return true;
	}

	/**
	 * Function to get the instance of Config module model
	 * @return <Settings_Vtiger_ConfigModule_Model> $moduleModel
	 */
	public static function getInstance($name = false)
	{
		$moduleModel = new self();
		$moduleModel->getViewableData();
		return $moduleModel;
	}
}
