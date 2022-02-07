<?php
/**
 * Record numbering basic view file.
 *
 * @package   Settings.View
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
/**
 * Record numbering basic view class.
 */
class Settings_RecordNumbering_CustomRecordNumbering_View extends Settings_Vtiger_Index_View
{
	/** {@inheritdoc} */
	protected $pageTitle = 'LBL_CUSTOMIZE_RECORD_NUMBERING';

	/** {@inheritdoc} */
	public function process(App\Request $request)
	{
		$qualifiedModuleName = $request->getModule(false);
		$supportedModules = Settings_RecordNumbering_Module_Model::getSupportedModules();
		$sourceModule = $request->getByType('sourceModule', 2);
		if ($sourceModule) {
			$defaultModuleModel = $supportedModules[\App\Module::getModuleId($sourceModule)];
		} else {
			$defaultModuleModel = reset($supportedModules);
		}
		$picklistFields = [];
		$picklistsModels = $defaultModuleModel->getFieldsByType(['picklist']);
		foreach ($picklistsModels as $fieldModel) {
			if (\App\Fields\Picklist::prefixExist($fieldModel->getFieldName())) {
				$picklistFields[$fieldModel->getName()] = $fieldModel;
			}
		}
		$referenceFields = [];
		$textParser = App\TextParser::getInstance($defaultModuleModel->getName());
		foreach ($textParser->getRelatedVariable('string') as $modules) {
			foreach ($modules as $blockName => $fields) {
				$blockName = \App\Language::translate($blockName, $defaultModuleModel->getName());
				foreach ($fields as $field) {
					$referenceFields[$blockName][$field['var_value']] = \App\Language::translate($field['label'], $defaultModuleModel->getName());
				}
			}
		}
		$viewer = $this->getViewer($request);
		$viewer->assign('SUPPORTED_MODULES', $supportedModules);
		$viewer->assign('DEFAULT_MODULE_MODEL', $defaultModuleModel);
		$viewer->assign('PICKLISTS', $picklistFields);
		$viewer->assign('REFERENCE_FIELDS', $referenceFields);
		$viewer->assign('IS_AJAX', $request->isAjax());
		$viewer->view('CustomRecordNumbering.tpl', $qualifiedModuleName);
	}

	/** {@inheritdoc} */
	public function getFooterScripts(App\Request $request)
	{
		return array_merge(parent::getFooterScripts($request), $this->checkAndConvertJsScripts([
			'libraries.clipboard.dist.clipboard',
			'modules.Settings.Vtiger.resources.Edit',
		]));
	}
}
