<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce Sp. z o.o.
 * *********************************************************************************** */

class Settings_Vtiger_ConfigModule_Model extends Settings_Vtiger_Module_Model
{
	public $listFields = [
		'upload_maxsize' => 'LBL_MAX_UPLOAD_SIZE',
		'default_module' => 'LBL_DEFAULT_MODULE',
		'listview_max_textlength' => 'LBL_MAX_TEXT_LENGTH_IN_LISTVIEW',
		'list_max_entries_per_page' => 'LBL_MAX_ENTRIES_PER_PAGE_IN_LISTVIEW',
		'defaultLayout' => 'LBL_DEFAULT_LAYOUT',
		'breadcrumbs' => 'LBL_SHOWING_BREADCRUMBS',
		'title_max_length' => 'LBL_TITLE_MAX_LENGTH',
		'MINIMUM_CRON_FREQUENCY' => 'LBL_MINIMUM_CRON_FREQUENCY',
		'listMaxEntriesMassEdit' => 'LBL_LIST_MAX_ENTRIES_MASSEDIT',
		'backgroundClosingModal' => 'LBL_BG_CLOSING_MODAL',
		'href_max_length' => 'LBL_HREF_MAX_LEGTH',
		'langInLoginView' => 'LBL_SHOW_LANG_IN_LOGIN_PAGE',
		'layoutInLoginView' => 'LBL_SHOW_LAYOUT_IN_LOGIN_PAGE'
	];

	/**
	 * Function to initiation.
	 *
	 * @throws \ReflectionException
	 */
	public function init()
	{
		foreach ($this->listFields as $fieldName => $fieldData) {
			$value = \App\Config::main($fieldName);
			if ('upload_maxsize' === $fieldName) {
				$value /= 1048576;
			}
			$this->set($fieldName, $value);
		}
	}

	/**
	 * Function to get CompanyDetails Menu item.
	 *
	 * @return menu item Model
	 */
	public function getMenuItem()
	{
		return Settings_Vtiger_MenuItem_Model::getInstance('LBL_CONFIG_EDITOR');
	}

	/**
	 * Function to get Edit view Url.
	 *
	 * @return string Url
	 */
	public function getEditViewUrl()
	{
		$menuItem = $this->getMenuItem();

		return 'index.php?module=Vtiger&parent=Settings&view=ConfigEditorEdit&block=' . $menuItem->get('blockid') . '&fieldid=' . $menuItem->get('fieldid');
	}

	/**
	 * Function to get Detail view Url.
	 *
	 * @return string Url
	 */
	public function getDetailViewUrl()
	{
		$menuItem = $this->getMenuItem();

		return 'index.php?module=Vtiger&parent=Settings&view=ConfigEditorDetail&block=' . $menuItem->get('blockid') . '&fieldid=' . $menuItem->get('fieldid');
	}

	/**
	 * Function to get the instance of Config module model.
	 *
	 * @throws \ReflectionException
	 *
	 * @return \Settings_Vtiger_ConfigModule_Model|\Settings_Vtiger_Module_Model
	 */
	public static function getInstance($name = 'Settings:Vtiger')
	{
		$moduleModel = new self();
		$moduleModel->init();
		return $moduleModel;
	}

	/**
	 * Function determines fields available in edition view.
	 *
	 * @param string $name
	 *
	 * @return \Settings_Vtiger_Field_Model
	 */
	public function getFieldInstanceByName($name)
	{
		$moduleName = $this->getName(true);
		$params = ['uitype' => 7, 'column' => $name, 'name' => $name, 'label' => $this->listFields[$name], 'displaytype' => 1, 'typeofdata' => 'I~M', 'presence' => 0, 'isEditableReadOnly' => false, 'maximumlength' => '', 'validator' => [['name' => 'NumberRange100']]];
		switch ($name) {
			case 'listMaxEntriesMassEdit':
				$params['maximumlength'] = '5000';
				$params['validator'] = [['name' => 'WholeNumberGreaterThanZero']];
				break;
			case 'upload_maxsize':
				$params['maximumlength'] = (string) round((vtlib\Functions::getMaxUploadSize() / 1048576), 0);
				unset($params['validator']);
				break;
			case 'layoutInLoginView':
			case 'langInLoginView':
			case 'backgroundClosingModal':
			case 'breadcrumbs':
				$params['uitype'] = 56;
				$params['typeofdata'] = 'C~M';
				unset($params['validator']);
				break;
			case 'default_module':
				$params['uitype'] = 16;
				unset($params['validator']);
				$params['picklistValues'] = ['Home' => \App\Language::translate('Home')];
				foreach (\vtlib\Functions::getAllModules(true, false, 0) as $module) {
					$params['picklistValues'][$module['name']] = \App\Language::translate($module['name'], $module['name']);
				}
				break;
			case 'defaultLayout':
				$params['uitype'] = 16;
				$params['picklistValues'] = \App\Layout::getAllLayouts();
				unset($params['validator']);
				break;
			default:
				break;
		}
		return Settings_Vtiger_Field_Model::init($moduleName, $params);
	}

	/**
	 * Function to getDisplay value of every field.
	 *
	 * @param string $name field name
	 *
	 * @return mixed
	 */
	public function getDisplayValue($name)
	{
		switch ($name) {
			case 'upload_maxsize':
				$value = $this->get($name) . ' ' . \App\Language::translate('LBL_MB', $this->getName(true));
				break;
			default:
				$value = $this->getFieldInstanceByName($name)->getDisplayValue($this->get($name));
				break;
		}
		return $value;
	}
}
