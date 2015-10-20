<?php
require_once 'modules/com_vtiger_workflow/expression_engine/VTExpressionsManager.inc';

/**
 * Module Class for PDF Settings
 * @package YetiForce.Model
 * @license licenses/License.html
 * @author Maciej Stencel <m.stencel@yetiforce.com>
 */
class Settings_PDF_Module_Model extends Settings_Vtiger_Module_Model
{

	public $baseTable = 'a_yf_pdf';
	public $baseIndex = 'pdfid';
	public $listFields = [
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
		'watermark_type',
		'watermark_text',
		'watermark_size',
		'watermark_angle',
		'template_members',
		'watermark_image'
	];
	public static $step1Fields = ['status', 'primary_name', 'secondary_name', 'module_name', 'metatags_status', 'meta_subject', 'meta_title', 'meta_author', 'meta_creator', 'meta_keywords'];
	public static $step2Fields = ['page_format', 'margin_chkbox', 'margin_top', 'margin_bottom', 'margin_left', 'margin_right', 'page_orientation', 'language', 'filename', 'visibility', 'default'];
	public static $step3Fields = ['module_name', 'header_content'];
	public static $step4Fields = ['module_name', 'body_content'];
	public static $step5Fields = ['footer_content'];
	public static $step6Fields = ['conditions',];
	public static $step7Fields = ['template_members'];
	public static $step8Fields = ['watermark_type', 'watermark_text', 'watermark_size', 'watermark_angle', 'watermark_image'];
	public static $module = 'PDF';
	public static $parent = 'Settings';
	static $metaVariables = array(
		'Current Date' => '(general : (__VtigerMeta__) date) ($_DATE_FORMAT_)',
		'Current Time' => '(general : (__VtigerMeta__) time)',
		'System Timezone' => '(general : (__VtigerMeta__) dbtimezone)',
		'User Timezone' => '(general : (__VtigerMeta__) usertimezone)',
		'CRM Detail View URL' => '(general : (__VtigerMeta__) crmdetailviewurl)',
		'Portal Detail View URL' => '(general : (__VtigerMeta__) portaldetailviewurl)',
		'Site Url' => '(general : (__VtigerMeta__) siteurl)',
		'Portal Url' => '(general : (__VtigerMeta__) portalurl)',
		'Record Id' => '(general : (__VtigerMeta__) recordId)',
		'LBL_HELPDESK_SUPPORT_NAME' => '(general : (__VtigerMeta__) supportName)',
		'LBL_HELPDESK_SUPPORT_EMAILID' => '(general : (__VtigerMeta__) supportEmailid)',
	);
	public static $uploadPath = 'layouts/vlayout/modules/Settings/PDF/resources/watermark_images/';
	protected $viewToPicklistValue = ['Detail' => 'PLL_DETAILVIEW', 'List' => 'PLL_LISTVIEW'];

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

	public static function getImportViewUrl()
	{
		return 'index.php?module=PDF&parent=Settings&view=Import';
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
		while ($row = $db->fetchByAssoc($result)) {
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

	public static function getRelatedModules($moduleName)
	{
		$db = PearDatabase::getInstance();
		$tabId = getTabid($moduleName);

		$referencedModules = [];
		$referenceUiTypes = ['10', '58', '51', '57', '59', '75', '80', '76', '73', '81', '53', '52', '78'];

		$query = 'SELECT `fieldid`, `uitype` FROM `vtiger_field` WHERE `tabid` = ? AND `uitype` IN (' . generateQuestionMarks($referenceUiTypes) . ');';
		$params = array_merge([$tabId], $referenceUiTypes);
		$result = $db->pquery($query, $params);

		while ($row = $db->fetchByAssoc($result)) {
			$uiType = $row['uitype'];
			$fieldId = $row['fieldid'];

			$moduleName = self::getReferencedModuleName($uiType, $fieldId);
			if (is_array($moduleName)) {
				foreach ($moduleName as $name) {
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

	public static function getReferencedModuleName($uiType, $fieldId)
	{
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

		switch ($uiType) {
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
				while ($row = $db->fetchByAssoc($result)) {
					$moduleName[] = $row['relmodule'];
				}
		}

		return $moduleName;
	}

	/**
	 * Return list of special functions for chosen module
	 * @param string $moduleName - name of the module
	 * @return array array of special functions
	 */
	public static function getSpecialFunctions($moduleName)
	{
		$specialFunctions = [];
		foreach (new DirectoryIterator('modules/Settings/PDF/special_functions') as $file) {
			if ($file->isFile() && $file->getExtension() == 'php' && $file->getFilename() != 'example.php') {
				include('modules/Settings/PDF/special_functions/' . $file->getFilename());
				$functionName = $file->getBasename('.php');
				if (in_array('all', $permittedModules) || in_array($moduleName, $permittedModules)) {
					$specialFunctions[$functionName] = vtranslate($functionName, self::$parent . ':' . self::$module);
				}
			}
		}
		return $specialFunctions;
	}

	/**
	 * Returns array containing company fields array - [fieldname => translatedname]
	 * @return array company fields with translated names
	 */
	public static function getCompanyFields()
	{
		$company = [];

		$companyDetails = getCompanyDetails();
		foreach ($companyDetails as $key => $value) {
			if ($key == 'organization_id') {
				continue;
			}
			$company[$key] = vtranslate($key, 'Settings:Vtiger');
		}

		return $company;
	}

	public function getModule()
	{
		return Vtiger_Module_Model::getCleanInstance($this->get('module_name'));
	}

	public static function getExpressions()
	{
		$db = PearDatabase::getInstance();

		$mem = new VTExpressionsManager($db);
		return $mem->expressionFunctions();
	}

	public static function getMetaVariables()
	{
		return self::$metaVariables;
	}

	/**
	 * Returns template records by module name
	 * @param string $moduleName - module name for which template was created
	 * @return array of template record models
	 */
	public function getTemplatesByModule($moduleName)
	{
		$db = PearDatabase::getInstance();

		$query = 'SELECT `' . $this->getBaseIndex() . '` FROM `' . $this->getBaseTable() . '` WHERE `module_name` = ? and `status` = ?;';
		$result = $db->pquery($query, [$moduleName, 'active']);
		$templates = [];

		while ($row = $db->fetchByAssoc($result)) {
			$templates[] = Settings_PDF_Record_Model::getInstanceById($row['pdfid']);
		}

		return $templates;
	}

	/**
	 * Check if pdf templates are avauble for this record, user and view
	 * @param integer $recordId - id of a record
	 * @param string $moduleName - name of the module
	 * @param string $view - modules view - Detail or List
	 * @return bool true or false
	 */
	public function checkPermissions($recordId, $moduleName, $view)
	{
		$templates = $this->getTemplatesForRecordId($recordId, $view, $moduleName);

		if (count($templates) > 0) {
			return true;
		} else {
			return false;
		}
	}

	public function getTemplatesForRecordId($recordId, $view, $moduleName = false)
	{
		if (!isRecordExists($recordId)) {
			return [];
		}
		if (!$moduleName) {
			$moduleName = Vtiger_Functions::getCRMRecordType($recordId);
		}

		// get the templates for chosen module
		$templates = $this->getTemplatesByModule($moduleName);
		if (count($templates) == 0) {
			return [];
		}

		// check template visibility
		if (!$this->removeInvisibleTemplates($templates, $view)) {
			return [];
		}

		// check filters
		if (!$this->removeFailingFilterTemplates($templates, $recordId)) {
			return [];
		}

		// check user permissions
		if (!$this->removeFailingPermissionTemplates($templates)) {
			return [];
		}

		return $templates;
	}

	public function removeInvisibleTemplates(&$templates, $view)
	{
		foreach ($templates as $id => &$template) {
			$visibility = explode(',', $template->get('visibility'));
			if (!in_array($this->viewToPicklistValue[$view], $visibility)) {
				unset($templates[$id]);
			}
		}

		if (empty($templates)) {
			return false;
		} else {
			return true;
		}
	}

	public function removeFailingFilterTemplates(&$templates, $recordId)
	{
		foreach ($templates as $id => &$template) {
			if (!$template->checkFiltersForRecord($recordId)) {
				unset($templates[$id]);
			}
		}

		if (empty($templates)) {
			return false;
		} else {
			return true;
		}
	}

	public function removeFailingPermissionTemplates(&$templates)
	{
		$currentUser = Users_Record_Model::getCurrentUserModel();
		$userGroups = new GetUserGroups();
		$userGroups->getAllUserGroups($currentUser->getId());

		foreach ($templates as $id => &$template) {
			if (!$template->checkUserPermissions($currentUser->getId(), $userGroups->user_groups)) {
				unset($templates[$id]);
			}
		}

		if (empty($templates)) {
			return false;
		} else {
			return true;
		}
	}

	public static function zipAndDownload(array $fileNames)
	{
		//create the object
		$zip = new ZipArchive();

		mt_srand(time());
		$postfix = time() . '_' . mt_rand(0, 1000);
		$zipPath = 'storage/';
		$zipName = "pdfZipFile_{$postfix}.zip";
		$fileName = $zipPath . $zipName;

		//create the file and throw the error if unsuccessful
		if ($zip->open($zipPath . $zipName, ZIPARCHIVE::CREATE) !== true) {
			exit("cannot open <$zipPath.$zipName>\n");
		}

		//add each files of $file_name array to archive
		foreach ($fileNames as $file) {
			$zip->addFile($file, basename($file));
		}
		$zip->close();

		// delete added pdf files
		foreach ($fileNames as $file) {
			unlink($file);
		}

		$finfo = finfo_open(FILEINFO_MIME_TYPE);
		$mimeType = finfo_file($finfo, $fileName);
		$size = filesize($fileName);
		$name = basename($fileName);

		header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");
		header("Content-Type: $mimeType");
		header('Content-Disposition: attachment; filename="' . $name . '";');
		header("Accept-Ranges: bytes");
		header('Content-Length: ' . $size);

		print readfile($fileName);

		// delete temporary zip file and saved pdf files
		unlink($fileName);
	}

	public static function attachToEmail($salt)
	{
		header('Location: index.php?module=OSSMail&view=compose&pdf_path='.$salt);
		exit;
	}
}
