<?php

/**
 * Edit View Class for PDF Settings
 * @package YetiForce.View
 * @license licenses/License.html
 * @author Maciej Stencel <m.stencel@yetiforce.com>
 */
class Settings_PDF_Edit_View extends Settings_Vtiger_Index_View
{

	public function process(Vtiger_Request $request)
	{
		$step = strtolower($request->getMode());
		$this->step($step, $request);
	}

	public function preProcess(Vtiger_Request $request)
	{
		parent::preProcess($request);
		$viewer = $this->getViewer($request);

		$recordId = $request->get('record');
		$viewer->assign('RECORDID', $recordId);
		if ($recordId) {
			$pdfModel = Settings_PDF_Record_Model::getInstanceById($recordId);
			$viewer->assign('PDF_MODEL', $pdfModel);
		}
		$viewer->assign('RECORD_MODE', $request->getMode());
		$viewer->view('EditHeader.tpl', $request->getModule(false));
	}

	public function step($step, Vtiger_Request $request)
	{
		$viewer = $this->getViewer($request);
		$moduleName = $request->getModule();
		$qualifiedModuleName = $request->getModule(false);

		$recordId = $request->get('record');
		if ($recordId) {
			$pdfModel = Settings_PDF_Record_Model::getInstanceById($recordId);
			$viewer->assign('RECORDID', $recordId);
			$viewer->assign('MODE', 'edit');
			$selectedModuleName = $pdfModel->get('module_name');
		} else {
			$selectedModuleName = 'Potentials'; //todo
			$pdfModel = Settings_PDF_Record_Model::getCleanInstance($moduleName);
			$fields = $pdfModel->getData();
			foreach ($fields as $name => $value) {
				$pdfModel->set($name, $request->get($name));
			}
		}

		$allModules = Settings_PDF_Module_Model::getSupportedModules();
		$viewer->assign('PDF_MODEL', $pdfModel);
		$viewer->assign('ALL_MODULES', $allModules);
		$viewer->assign('MODULE', $moduleName);
		$viewer->assign('QUALIFIED_MODULE', $qualifiedModuleName);
		$viewer->assign('SOURCE_MODULE', $selectedModuleName);

		switch ($step) {
			case 'step8':
				$viewer->view('Step8.tpl', $qualifiedModuleName);
				break;

			case 'step7':
				$viewer->view('Step7.tpl', $qualifiedModuleName);
				break;

			case 'step6':
				$recordStructureInstance = Settings_PDF_RecordStructure_Model::getInstanceForPDFModule($pdfModel, Settings_PDF_RecordStructure_Model::RECORD_STRUCTURE_MODE_FILTER);

				$viewer->assign('RECORD_STRUCTURE_MODEL', $recordStructureInstance);
				$recordStructure = $recordStructureInstance->getStructure();
				if (in_array($selectedModuleName, getInventoryModules())) {
					$itemsBlock = "LBL_ITEM_DETAILS";
					unset($recordStructure[$itemsBlock]);
				}
				$viewer->assign('RECORD_STRUCTURE', $recordStructure);

				$viewer->assign('MODULE_MODEL', Settings_PDF_RecordStructure_Model::getModule());
				$viewer->assign('SELECTED_MODULE_NAME', $selectedModuleName);

				$dateFilters = Vtiger_Field_Model::getDateFilterTypes();
				foreach ($dateFilters as $comparatorKey => $comparatorInfo) {
					$comparatorInfo['startdate'] = DateTimeField::convertToUserFormat($comparatorInfo['startdate']);
					$comparatorInfo['enddate'] = DateTimeField::convertToUserFormat($comparatorInfo['enddate']);
					$comparatorInfo['label'] = vtranslate($comparatorInfo['label'], $qualifiedModuleName);
					$dateFilters[$comparatorKey] = $comparatorInfo;
				}
				$viewer->assign('DATE_FILTERS', $dateFilters);
				$viewer->assign('ADVANCED_FILTER_OPTIONS', Settings_Workflows_Field_Model::getAdvancedFilterOptions());
				$viewer->assign('ADVANCED_FILTER_OPTIONS_BY_TYPE', Settings_Workflows_Field_Model::getAdvancedFilterOpsByFieldType());
				$viewer->assign('COLUMNNAME_API', 'getWorkFlowFilterColumnName');

				$viewer->assign('FIELD_EXPRESSIONS', Settings_Workflows_Module_Model::getExpressions());
				$viewer->assign('META_VARIABLES', Settings_Workflows_Module_Model::getMetaVariables());


				$viewer->assign('ADVANCE_CRITERIA', $pdfModel->transformToAdvancedFilterCondition());
				$viewer->view('Step6.tpl', $qualifiedModuleName);
				break;

			case 'step5':
				$relatedModules = Settings_PDF_Module_Model::getRelatedModules($pdfModel->get('module_name'));
				if (count($relatedModules) > 0) {
					$relatedFields = Settings_PDF_Module_Model::getMainModuleFields(key($relatedModules));
					$specialFunctions = Settings_PDF_Module_Model::getSpecialFunctions($allModules[key($allModules)]->getName());
				} else {
					$relatedFields = [];
					$specialFunctions = [];
				}

				$viewer->assign('RELATED_MODULES', $relatedModules);
				$viewer->assign('RELATED_FIELDS', $relatedFields);
				$viewer->assign('SPECIAL_FUNCTIONS', $specialFunctions);
				$viewer->view('Step5.tpl', $qualifiedModuleName);
				break;

			case 'step4':
				$relatedModules = Settings_PDF_Module_Model::getRelatedModules($pdfModel->get('module_name'));
				if (count($relatedModules) > 0) {
					$relatedFields = Settings_PDF_Module_Model::getMainModuleFields(key($relatedModules));
					$specialFunctions = Settings_PDF_Module_Model::getSpecialFunctions($allModules[key($allModules)]->getName());
				} else {
					$relatedFields = [];
					$specialFunctions = [];
				}

				$viewer->assign('RELATED_MODULES', $relatedModules);
				$viewer->assign('RELATED_FIELDS', $relatedFields);
				$viewer->assign('SPECIAL_FUNCTIONS', $specialFunctions);
				$viewer->view('Step4.tpl', $qualifiedModuleName);
				break;

			case 'step3':
				$relatedModules = Settings_PDF_Module_Model::getRelatedModules($pdfModel->get('module_name'));
				if (count($relatedModules) > 0) {
					$relatedFields = Settings_PDF_Module_Model::getMainModuleFields(key($relatedModules));
					$specialFunctions = Settings_PDF_Module_Model::getSpecialFunctions($allModules[key($allModules)]->getName());
				} else {
					$relatedFields = [];
					$specialFunctions = [];
				}

				$viewer->assign('RELATED_MODULES', $relatedModules);
				$viewer->assign('RELATED_FIELDS', $relatedFields);
				$viewer->assign('SPECIAL_FUNCTIONS', $specialFunctions);
				$viewer->view('Step3.tpl', $qualifiedModuleName);
				break;

			case 'step2':
				$viewer->view('Step2.tpl', $qualifiedModuleName);
				break;

			case 'step1':
			default:
				$selectedModule = $request->get('source_module');
				if (!empty($selectedModule)) {
					$viewer->assign('SELECTED_MODULE', $selectedModule);
				} else {
					$viewer->assign('SELECTED_MODULE', $pdfModel->get('module_name'));
				}
				$viewer->view('Step1.tpl', $qualifiedModuleName);
				break;
		}
	}

	public function getFooterScripts(Vtiger_Request $request)
	{
		$headerScriptInstances = parent::getFooterScripts($request);
		$moduleName = $request->getModule();

		$jsFileNames = array(
			'libraries.jquery.ZeroClipboard.ZeroClipboard',
			'modules.Settings.Vtiger.resources.Edit',
			"modules.Settings.$moduleName.resources.Edit",
			"modules.Settings.$moduleName.resources.Edit1",
			"modules.Settings.$moduleName.resources.Edit2",
			"modules.Settings.$moduleName.resources.Edit3",
			"modules.Settings.$moduleName.resources.Edit4",
			"modules.Settings.$moduleName.resources.Edit5",
			"modules.Settings.$moduleName.resources.Edit6",
			"modules.Settings.$moduleName.resources.Edit7",
			"modules.Settings.$moduleName.resources.Edit8",
			"modules.Settings.$moduleName.resources.AdvanceFilter",
//			'layouts.vlayout.modules.Vtiger.resources.AdvanceFilter'
		);

		$jsScriptInstances = $this->checkAndConvertJsScripts($jsFileNames);
		$headerScriptInstances = array_merge($headerScriptInstances, $jsScriptInstances);
		return $headerScriptInstances;
	}

	function getHeaderCss(Vtiger_Request $request)
	{
		$headerCssInstances = parent::getHeaderCss($request);
		$moduleName = $request->getModule();
		$cssFileNames = array(
			"~layouts/vlayout/modules/Settings/$moduleName/Edit.css",
		);
		$cssInstances = $this->checkAndConvertCssStyles($cssFileNames);
		$headerCssInstances = array_merge($cssInstances, $headerCssInstances);
		return $headerCssInstances;
	}
}
