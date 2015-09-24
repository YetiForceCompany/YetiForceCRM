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
		'meta_author' => 'LBL_META_AUTHOR',
		'meta_creator' => 'LBL_META_CREATOR',
		'meta_keywords' => 'LBL_META_KEYWORDS',
		'margin_chkbox' => 'LBL_MAIN_MARGIN',
		'page_format' => 'LBL_PAGE_FORMAT'
	];
	public static $allFields = [
		'module_name',
		'status',
		'primary_name',
		'secondary_name',
		'meta_author',
		'meta_creator',
		'meta_keywords',
		'metatags_status',
		'meta_subject',
		'meta_title',
		'page_format',
		'margin_chkbox',
		'margin_top',
		'margin_bottom',
		'margin_left',
		'margin_right',
		'page_orientation',
		'language',
		'filename',
		'visibility',
		'default',
		'header_content',
		'body_content',
		'footer_content',
		'conditions',
		'cole',
		'colf',
		'colg'
	];
	public static $step1Fields = ['status', 'primary_name', 'secondary_name', 'module_name', 'metatags_status', 'meta_subject', 'meta_title', 'meta_author', 'meta_creator', 'meta_keywords'];
	public static $step2Fields = ['page_format', 'margin_chkbox', 'margin_top', 'margin_bottom', 'margin_left', 'margin_right', 'page_orientation', 'language', 'filename', 'visibility', 'default'];
	public static $step3Fields = ['module_name', 'header_content'];
	public static $step4Fields = ['module_name', 'body_content'];
	public static $step5Fields = ['footer_content'];
	public static $step6Fields = ['conditions',];
	public static $step7Fields = ['colf'];
	public static $step8Fields = ['colg'];
	public static $module = 'PDF';
	public static $parent = 'Settings';

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
		return "javascript:Settings_PDF_List_Js.triggerCreate('" . self::getCreateRecordUrl() . "')";
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

	public static function getFieldsByStep($step = 1)
	{
		switch ($step) {
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

	public static function getPageFormats()
	{
		return [
			'4A0',
			'2A0',
			'A0', 'A1', 'A2', 'A3', 'A4', 'A5', 'A6', 'A7', 'A8', 'A9', 'A10',
			'B0', 'B1', 'B2', 'B3', 'B4', 'B5', 'B6', 'B7', 'B8', 'B9', 'B10',
			'C0', 'C1', 'C2', 'C3', 'C4', 'C5', 'C6', 'C7', 'C8', 'C9', 'C10',
			'RA0', 'RA1', 'RA2', 'RA3', 'RA4',
			'SRA0', 'SRA1', 'SRA2', 'SRA3', 'SRA4',
			'LETTER',
			'LEGAL',
			'LEDGER',
			'TABLOID',
			'EXECUTIVE',
			'FOLIO',
			'B', //	'B' format paperback size 128x198mm
			'A', //	'A' format paperback size 111x178mm
			'DEMY', //	'Demy' format paperback size 135x216mm
			'ROYAL' //	'Royal' format paperback size 153x234mm
		];
	}

	public static function getMainModuleFields($moduleName)
	{
		$db = PearDatabase::getInstance();
		$tabId = getTabid($moduleName);
		$query = 'SELECT `fieldid`, `fieldlabel`, `fieldname`, `uitype`, `block` FROM `vtiger_field` WHERE `tabid` = ? AND `presence` != ? AND `typeofdata` != ? AND `block` NOT IN (?) ORDER BY `block` ASC;';
		$result = $db->pquery($query, [$tabId, 1, 'P~M', 0]);
		$output = [];
		$currentBlockId = '';
		$currentBlockName = '';
		$i = 0;
		while($row = $db->fetchByAssoc($result)) {
			if ($currentBlockId != $row['block']) {
				$currentBlockName = vtranslate(getBlockName($row['block']), $moduleName);
			}
			$currentBlockId = $row['block'];

			$output[$currentBlockName][$i]['label'] = vtranslate($row['fieldlabel'], $moduleName);
			$output[$currentBlockName][$i]['id'] = $row['fieldid'];
			$output[$currentBlockName][$i]['name'] = $row['fieldname'];
			$output[$currentBlockName][$i]['uitype'] = $row['uitype'];
			$i++;
		}

		return $output;
	}

	public static function getRelatedModules($moduleName) {
		$db = PearDatabase::getInstance();
		$tabId = getTabid($moduleName);

		$referencedModules = [];
		$referenceUiTypes = ['10', '58', '51', '57', '59', '75', '80', '76', '73', '81', '53', '52', '78'];

		$query = 'SELECT `fieldid`, `uitype` FROM `vtiger_field` WHERE `tabid` = ? AND `uitype` IN ('.generateQuestionMarks($referenceUiTypes).');';
		$params = array_merge([$tabId], $referenceUiTypes);
		$result = $db->pquery($query, $params);

		while($row = $db->fetchByAssoc($result)) {
			$uiType = $row['uitype'];
			$fieldId = $row['fieldid'];

			$moduleName = self::getReferencedModuleName($uiType, $fieldId);
			if (is_array($moduleName)) {
				foreach($moduleName as $name) {
					if (!in_array($name, $referencedModules)) {
						$referencedModules[$name] = vtranslate($name, $name);
					}
				}
			} elseif (!in_array($moduleName, $referencedModules)) {
				$referencedModules[$moduleName] = vtranslate($moduleName, $moduleName);
			}
		}
		asort($referencedModules);
		return $referencedModules;
	}

	protected static function getReferencedModuleName($uiType, $fieldId) {
		$moduleName = '';
		$referenceToModule = [
			'51' => 'Accounts',
			'52' => 'Users',
			'53' => 'Users',
			'57' => 'Contacts',
			'58' => 'Campaigns',
			'59' => 'Products',
			'73' => 'Accounts',
			'75' => 'Vendors',
			'76' => 'Potentials',
			'78' => 'Quotes',
			'80' => 'SalesOrder',
			'81' => 'Vendors'
		];

		switch($uiType) {
			case 51:
			case 52:
			case 53:
			case 57:
			case 58:
			case 59:
			case 73:
			case 75:
			case 76:
			case 78:
			case 80:
			case 81:
				$moduleName = $referenceToModule[$uiType];
				break;

			case 10:
			default:
				$db = PearDatabase::getInstance();
				$query = 'SELECT DISTINCT `relmodule` FROM `vtiger_fieldmodulerel` WHERE `fieldid` = ?;';
				$result = $db->pquery($query, [$fieldId]);
				while($row = $db->fetchByAssoc($result)) {
					$moduleName[] = $row['relmodule'];
				}
		}

		return $moduleName;
	}

	public static function getSpecialFunctions($moduleName)
	{
		$specialFunctions = [];
		foreach (new DirectoryIterator('modules/Settings/PDF/special_functions') as $file) {
			if ($file->isFile() && $file->getExtension() == 'php' && $file->getFilename() != 'example.php') {
				include_once('modules/Settings/PDF/special_functions/'.$file->getFilename());
				$functionName = $file->getBasename('.php');
				if (in_array('all', $permitted_modules) || in_array($moduleName, $permitted_modules)) {
					$specialFunctions['#special_functions#'.$functionName.'#special_function#'] = vtranslate($functionName, self::$parent.':'.self::$module);
				}
			}
		}
		return $specialFunctions;
	}

	public static function getCompanyFields() {
		$db = PearDatabase::getInstance();
		$company = [];

		$companyDetails = getCompanyDetails();
		foreach($companyDetails as $key => $value) {
			if ($key == 'organization_id') {
				continue;
			}
			$company[$key] = vtranslate($key, 'Settings:Vtiger');
		}

		return $company;
	}

	public function getModule() {
		return Vtiger_Module_Model::getCleanInstance($this->get('module_name'));
	}
}
