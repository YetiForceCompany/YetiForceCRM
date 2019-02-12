<?php

/**
 * Module Class for PDF Settings.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Maciej Stencel <m.stencel@yetiforce.com>
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
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
		'meta_keywords' => 'LBL_META_KEYWORDS',
		'margin_chkbox' => 'LBL_MAIN_MARGIN',
		'page_format' => 'LBL_PAGE_FORMAT',
	];
	public static $allFields = [
		'module_name',
		'status',
		'primary_name',
		'secondary_name',
		'meta_author',
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
		'header_height',
		'footer_height',
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
		'watermark_angle',
		'template_members',
		'watermark_image',
		'one_pdf',
	];
	public static $step1Fields = ['status', 'primary_name', 'secondary_name', 'module_name', 'metatags_status', 'meta_subject', 'meta_title', 'meta_author', 'meta_keywords', 'page_format', 'margin_chkbox', 'margin_top', 'margin_bottom', 'margin_left', 'margin_right', 'header_height', 'footer_height', 'page_orientation', 'language', 'filename', 'visibility', 'default', 'one_pdf', 'template_members', 'watermark_type', 'watermark_text', 'watermark_image', 'watermark_angle'];
	public static $step2Fields = ['module_name', 'header_content', 'module_name', 'body_content', 'footer_content'];
	public static $step3Fields = ['conditions'];
	public static $module = 'PDF';
	public static $parent = 'Settings';
	public static $uploadPath = 'storage/Pdf/watermark/';
	protected $viewToPicklistValue = ['Detail' => 'PLL_DETAILVIEW', 'List' => 'PLL_LISTVIEW'];

	/**
	 * Function to get the url for default view of the module.
	 *
	 * @return string - url
	 */
	public static function getDefaultUrl()
	{
		return 'index.php?module=PDF&parent=Settings&view=List';
	}

	/**
	 * Function to get the url for create view of the module.
	 *
	 * @return string - url
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

	public static function getFieldsByStep($step = 1)
	{
		switch ($step) {
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
			'ROYAL', //	'Royal' format paperback size 153x234mm
		];
	}

	/**
	 * Returns template records by module name.
	 *
	 * @param string $moduleName - module name for which template was created
	 *
	 * @return array of template record models
	 */
	public function getTemplatesByModule($moduleName)
	{
		return Vtiger_PDF_Model::getTemplatesByModule($moduleName);
	}
}
