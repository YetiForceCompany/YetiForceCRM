<?php
/**
 * Config editor basic module file.
 *
 * @package   Settings.Model
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
/**
 * Config editor basic module class.
 */
class Settings_ConfigEditor_Module_Model extends Settings_Vtiger_Module_Model
{
	/** {@inheritdoc} */
	public $name = 'ConfigEditor';
	/** {@inheritdoc} */
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
		'layoutInLoginView' => 'LBL_SHOW_LAYOUT_IN_LOGIN_PAGE',
	];
	/** @var array Fields for performance */
	public $performanceFields = [
		'MAX_NUMBER_EXPORT_RECORDS' => 'LBL_MAX_NUMBER_EXPORT_RECORDS'
	];

	/** @var array Fields for relation */
	public $relationFields = [
		'SHOW_RELATED_MODULE_NAME' => 'LBL_RELATION_SHOW_RELATED_MODULE_NAME',
		'SHOW_RELATED_ICON' => 'LBL_RELATION_SHOW_RELATED_ICON',
		'SHOW_RECORDS_COUNT' => 'LBL_RELATION_SHOW_RECORDS_COUNT',
		'COMMENT_MAX_LENGTH' => 'LBL_RELATION_COMMENT_MAX_LENGTH',
		'separateChangeRelationButton' => 'LBL_RELATION_SEPARATE_CHANGE_RELATION_BUTTON',
	];

	/** @var string Configuration type */
	public $type;

	/**
	 * Function to initiation.
	 *
	 * @param string $type
	 *
	 * @throws \ReflectionException
	 */
	public function init(string $type = 'Main')
	{
		$this->type = $type;
		foreach (array_keys($this->getEditFields()) as $fieldName) {
			$source = $this->getFieldInstanceByName($fieldName)->get('source');
			$value = \App\Config::{$source}($fieldName);
			if ('upload_maxsize' === $fieldName) {
				$value /= 1048576;
			}
			$this->set($fieldName, $value);
		}
		return $this;
	}

	/**
	 * Gets fields for edit.
	 *
	 * @param mixed|null $configName
	 *
	 * @return array
	 */
	public function getEditFields($configName = null): array
	{
		$fields = [];
		switch ($configName ?? $this->type) {
			case 'Main':
				$fields = $this->listFields;
				break;
			case 'Relation':
				$fields = $this->relationFields;
				break;
			case 'Performance':
				$fields = $this->performanceFields;
				break;
			default:
				break;
		}
		return $fields;
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

		return 'index.php?module=ConfigEditor&parent=Settings&view=Edit&block=' . $menuItem->get('blockid') . '&fieldid=' . $menuItem->get('fieldid');
	}

	/**
	 * Function to get Detail view Url.
	 *
	 * @return string Url
	 */
	public function getDetailViewUrl()
	{
		$menuItem = $this->getMenuItem();

		return 'index.php?module=ConfigEditor&parent=Settings&view=Detail&block=' . $menuItem->get('blockid') . '&fieldid=' . $menuItem->get('fieldid');
	}

	/**
	 * Function to get the instance of Config module model.
	 *
	 * @param mixed $name
	 *
	 * @throws \ReflectionException
	 *
	 * @return self
	 */
	public static function getInstance($name = 'Settings:Vtiger')
	{
		return new self();
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
		$params = ['uitype' => 7, 'column' => $name, 'name' => $name,  'displaytype' => 1, 'typeofdata' => 'I~M', 'presence' => 0, 'isEditableReadOnly' => false, 'maximumlength' => '', 'validator' => [['name' => 'NumberRange100']], 'source' => 'main'];
		switch ($name) {
			case 'MAX_NUMBER_EXPORT_RECORDS':
				$params['label'] = $this->performanceFields[$name];
				$params['validator'] = [['name' => 'WholeNumberGreaterThanZero']];
				$params['uitype'] = 7;
				$params['maximumlength'] = '99999999';
				$params['source'] = 'performance';
				$params['purifyType'] = \App\Purifier::TEXT;
				$params['fieldvalue'] = $this->get($name);
				break;
			case 'listMaxEntriesMassEdit':
				$params['maximumlength'] = '5000';
				$params['validator'] = [['name' => 'WholeNumberGreaterThanZero']];
				$params['label'] = $this->listFields[$name];
				$params['purifyType'] = \App\Purifier::TEXT;
				break;
			case 'upload_maxsize':
				$params['label'] = $this->listFields[$name];
				$params['purifyType'] = \App\Purifier::TEXT;
				$params['maximumlength'] = (string) round(\App\Config::getMaxUploadSize(false, true), 0) ?: '';
				unset($params['validator']);
				break;
			case 'layoutInLoginView':
			case 'langInLoginView':
			case 'backgroundClosingModal':
				$params['label'] = $this->listFields[$name];
				$params['uitype'] = 56;
				$params['typeofdata'] = 'C~M';
				$params['purifyType'] = \App\Purifier::BOOL;
				unset($params['validator']);
				break;
			case 'breadcrumbs':
				$params['label'] = $this->listFields[$name];
				$params['uitype'] = 56;
				$params['typeofdata'] = 'C~M';
				$params['source'] = 'layout';
				$params['purifyType'] = \App\Purifier::BOOL;
				unset($params['validator']);
				break;
			case 'default_module':
				$params['label'] = $this->listFields[$name];
				$params['uitype'] = 16;
				$params['maximumlength'] = '40';
				unset($params['validator']);
				$params['picklistValues'] = ['Home' => \App\Language::translate('Home')];
				$params['purifyType'] = \App\Purifier::TEXT;
				foreach (\vtlib\Functions::getAllModules(true, false, 0) as $module) {
					$params['picklistValues'][$module['name']] = \App\Language::translate($module['name'], $module['name']);
				}
				break;
			case 'defaultLayout':
				$params['label'] = $this->listFields[$name];
				$params['uitype'] = 16;
				$params['maximumlength'] = '50';
				$params['picklistValues'] = \App\Layout::getAllLayouts();
				$params['purifyType'] = \App\Purifier::TEXT;
				unset($params['validator']);
				break;
			// Realtion
			case 'COMMENT_MAX_LENGTH':
				$params['label'] = $this->relationFields[$name];
				$params['uitype'] = 7;
				$params['maximumlength'] = '200';
				$params['validator'] = [['name' => 'WholeNumberGreaterThanZero']];
				$params['source'] = 'relation';
				$params['tooltip'] = 'LBL_RELATION_COMMENT_MAX_LENGTH_DESC';
				$params['fieldvalue'] = $this->get($name);
				$params['purifyType'] = \App\Purifier::TEXT;
				break;
			case 'SHOW_RELATED_MODULE_NAME':
			case 'SHOW_RELATED_ICON':
			case 'SHOW_RECORDS_COUNT':
				$params['label'] = $this->relationFields[$name];
				$params['uitype'] = 56;
				$params['typeofdata'] = 'C~M';
				$params['source'] = 'relation';
				$params['fieldvalue'] = $this->get($name);
				$params['purifyType'] = \App\Purifier::BOOL;
				unset($params['validator']);
				break;
			case 'separateChangeRelationButton':
				$params['label'] = $this->relationFields[$name];
				$params['uitype'] = 56;
				$params['typeofdata'] = 'C~M';
				$params['source'] = 'relation';
				$params['fieldvalue'] = $this->get($name);
				$params['tooltip'] = 'LBL_RELATION_SEPARATE_CHANGE_RELATION_BUTTON_DESC';
				$params['purifyType'] = \App\Purifier::BOOL;
				unset($params['validator']);
				break;
			case 'title_max_length':
			case 'MINIMUM_CRON_FREQUENCY':
				$params['uitype'] = 7;
				$params['purifyType'] = \App\Purifier::TEXT;
				$params['maximumlength'] = '0,100';
				break;
			case 'listview_max_textlength':
			case 'list_max_entries_per_page':
			case 'href_max_length':
				$params['uitype'] = 7;
				$params['purifyType'] = \App\Purifier::TEXT;
				$params['maximumlength'] = '255';
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
