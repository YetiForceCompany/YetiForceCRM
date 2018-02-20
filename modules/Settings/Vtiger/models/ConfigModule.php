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
	 * Function to read config file.
	 *
	 * @return array The data of config file
	 */
	public function readFile()
	{
		if (!$this->completeData) {
			$this->completeData = file_get_contents($this->fileName);
		}

		return $this->completeData;
	}

	/**
	 * Function to get CompanyDetails Menu item.
	 *
	 * @return menu item Model
	 */
	public function getMenuItem()
	{
		$menuItem = Settings_Vtiger_MenuItem_Model::getInstance('LBL_CONFIG_EDITOR');

		return $menuItem;
	}

	/**
	 * Function to get Edit view Url.
	 *
	 * @return string Url
	 */
	public function getEditViewUrl()
	{
		$menuItem = $this->getMenuItem();

		return '?module=Vtiger&parent=Settings&view=ConfigEditorEdit&block=' . $menuItem->get('blockid') . '&fieldid=' . $menuItem->get('fieldid');
	}

	/**
	 * Function to get Detail view Url.
	 *
	 * @return string Url
	 */
	public function getDetailViewUrl()
	{
		$menuItem = $this->getMenuItem();

		return '?module=Vtiger&parent=Settings&view=ConfigEditorDetail&block=' . $menuItem->get('blockid') . '&fieldid=' . $menuItem->get('fieldid');
	}

	/**
	 * Function to get Viewable data of config details.
	 *
	 * @return array
	 */
	public function getViewableData()
	{
		if (!$this->getData()) {
			$fileContent = $this->readFile();
			$pattern = '/\$([^=]+)=([^;]+);/';
			$matches = null;
			$matchesFound = preg_match_all($pattern, $fileContent, $matches);
			$configContents = [];
			if ($matchesFound) {
				$configContents = $matches[0];
			}
			$data = [];
			$editableFields = $this->getEditableFields();
			foreach ($editableFields as $fieldName => $fieldDetails) {
				foreach ($configContents as $configContent) {
					if (strpos($configContent, $fieldName)) {
						$fieldValue = explode(' = ', $configContent);
						$fieldValue = $fieldValue[1];
						if ($fieldName === 'upload_maxsize') {
							$fieldValue = round(number_format($fieldValue / 1048576, 2));
						}

						$data[$fieldName] = str_replace(';', '', str_replace("'", '', $fieldValue));
						break;
					}
				}
			}
			$this->setData($data);
		}

		return $this->getData();
	}

	/**
	 * Function to get picklist values.
	 *
	 * @param string $fieldName
	 *
	 * @return array list of module names
	 */
	public function getPicklistValues($fieldName)
	{
		if ($fieldName === 'default_module') {
			$presence = [0];
			$restrictedModules = ['Integration', 'Dashboard'];
			$query = (new \App\Db\Query())->select(['name', 'tablabel'])->from('vtiger_tab')->where(['presence' => $presence, 'isentitytype' => 1])->andWhere(['not in', 'name', $restrictedModules]);
			$rows = $query->createCommand()->queryAllByGroup(0);

			return array_merge(['Home' => 'Home'], $rows);
		} elseif ($fieldName === 'defaultLayout') {
			return \App\Layout::getAllLayouts();
		}

		return ['true', 'false'];
	}

	/**
	 * Function to get editable fields.
	 *
	 * @return array list of field names
	 */
	public function getEditableFields()
	{
		return [
			'upload_maxsize' => ['label' => 'LBL_MAX_UPLOAD_SIZE', 'fieldType' => 'input'],
			'default_module' => ['label' => 'LBL_DEFAULT_MODULE', 'fieldType' => 'picklist'],
			'listview_max_textlength' => ['label' => 'LBL_MAX_TEXT_LENGTH_IN_LISTVIEW', 'fieldType' => 'input'],
			'list_max_entries_per_page' => ['label' => 'LBL_MAX_ENTRIES_PER_PAGE_IN_LISTVIEW', 'fieldType' => 'input'],
			'defaultLayout' => ['label' => 'LBL_DEFAULT_LAYOUT', 'fieldType' => 'picklist'],
			'breadcrumbs' => ['label' => 'LBL_SHOWING_BREADCRUMBS', 'fieldType' => 'checkbox'],
			'title_max_length' => ['label' => 'LBL_TITLE_MAX_LENGTH', 'fieldType' => 'input'],
			'MINIMUM_CRON_FREQUENCY' => ['label' => 'LBL_MINIMUM_CRON_FREQUENCY', 'fieldType' => 'input'],
			'listMaxEntriesMassEdit' => ['label' => 'LBL_LIST_MAX_ENTRIES_MASSEDIT', 'fieldType' => 'input'],
			'backgroundClosingModal' => ['label' => 'LBL_BG_CLOSING_MODAL', 'fieldType' => 'checkbox'],
			'href_max_length' => ['label' => 'LBL_HREF_MAX_LEGTH', 'fieldType' => 'input'],
			'langInLoginView' => ['label' => 'LBL_SHOW_LANG_IN_LOGIN_PAGE', 'fieldType' => 'checkbox'],
			'layoutInLoginView' => ['label' => 'LBL_SHOW_LAYOUT_IN_LOGIN_PAGE', 'fieldType' => 'checkbox'],
		];
	}

	/**
	 * Function to save the data.
	 */
	public function save()
	{
		$fileContent = $this->completeData;
		$updatedFields = $this->get('updatedFields');
		$validationInfo = $this->validateFieldValues($updatedFields);
		if ($validationInfo === true) {
			foreach ($updatedFields as $fieldName => $fieldValue) {
				if ($fieldName === 'upload_maxsize') {
					$fieldValue = $fieldValue * 1048576; //(1024 * 1024)
				} elseif (in_array($fieldName, ['title_max_length', 'listview_max_textlength', 'listMaxEntriesMassEdit', 'list_max_entries_per_page', 'MINIMUM_CRON_FREQUENCY', 'href_max_length'])) {
					$fieldValue = (int) $fieldValue;
				} elseif (in_array($fieldName, ['layoutInLoginView', 'langInLoginView', 'backgroundClosingModal', 'breadcrumbs'])) {
					$fieldValue = strcasecmp('true', (string) $fieldValue) === 0;
				}
				$replacement = sprintf('$%s = %s;', $fieldName, \App\Utils::varExport($fieldValue));
				$fileContent = preg_replace('/\$' . $fieldName . '[\s]+=([^;]+);/', $replacement, $fileContent);
			}
			$filePointer = fopen($this->fileName, 'w');
			fwrite($filePointer, $fileContent);
			fclose($filePointer);
		}

		return $validationInfo;
	}

	/**
	 * Function to validate the field values.
	 *
	 * @param array $updatedFields
	 *
	 * @return bool|string True/Error message
	 */
	public function validateFieldValues($updatedFields)
	{
		if (!in_array($updatedFields['default_module'], $this->getPicklistValues('default_module'))) {
			return 'LBL_INVALID_MODULE';
		} elseif (!filter_var(ltrim($updatedFields['upload_maxsize'], '0'), FILTER_VALIDATE_INT) ||
			!filter_var(ltrim($updatedFields['list_max_entries_per_page'], '0'), FILTER_VALIDATE_INT) ||
			!filter_var(ltrim($updatedFields['title_max_length'], '0'), FILTER_VALIDATE_INT) ||
			!filter_var(ltrim($updatedFields['listMaxEntriesMassEdit'], '0'), FILTER_VALIDATE_INT) ||
			!filter_var(ltrim($updatedFields['href_max_length'], '0'), FILTER_VALIDATE_INT) ||
			!filter_var(ltrim($updatedFields['MINIMUM_CRON_FREQUENCY'], '0'), FILTER_VALIDATE_INT) ||
			!filter_var(ltrim($updatedFields['listview_max_textlength'], '0'), FILTER_VALIDATE_INT)) {
			return 'LBL_INVALID_NUMBER';
		}
		if (array_diff(array_keys($updatedFields), array_keys($this->getEditableFields()))) {
			return false;
		}

		return true;
	}

	/**
	 * Function to get the instance of Config module model.
	 *
	 * @return /self $moduleModel
	 */
	public static function getInstance($name = false)
	{
		$moduleModel = new self();
		$moduleModel->getViewableData();

		return $moduleModel;
	}
}
