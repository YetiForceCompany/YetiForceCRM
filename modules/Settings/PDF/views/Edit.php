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
		$mode = $request->getMode();
		if ($mode) {
			$this->$mode($request);
		} else {
			$this->step1($request);
		}
	}

	public function preProcess(Vtiger_Request $request)
	{
		parent::preProcess($request);
		$viewer = $this->getViewer($request);

		$recordId = $request->get('record');
		$viewer->assign('RECORDID', $recordId);
		if ($recordId) {
			$pdfModel = Settings_PDF_Record_Model::getInstance($recordId);
			$viewer->assign('PDF_MODEL', $pdfModel);
		}
		$viewer->assign('RECORD_MODE', $request->getMode());
		$viewer->view('EditHeader.tpl', $request->getModule(false));
	}

	public function step1(Vtiger_Request $request)
	{
		$viewer = $this->getViewer($request);
		$moduleName = $request->getModule();
		$qualifiedModuleName = $request->getModule(false);

		$recordId = $request->get('record');
		if ($recordId) {
			$pdfModel = Settings_PDF_Record_Model::getInstance($recordId);
			$viewer->assign('RECORDID', $recordId);
			$viewer->assign('MODULE_MODEL', $pdfModel->getModule());
			$viewer->assign('MODE', 'edit');
		} else {
			$pdfModel = Settings_PDF_Record_Model::getCleanInstance($moduleName);
			$selectedModule = $request->get('source_module');
			if (!empty($selectedModule)) {
				$viewer->assign('SELECTED_MODULE', $selectedModule);
			}
		}

		$viewer->assign('PDF_MODEL', $pdfModel);
		$viewer->assign('ALL_MODULES', Settings_PDF_Module_Model::getSupportedModules());

		$viewer->assign('MODULE', $moduleName);
		$viewer->assign('QUALIFIED_MODULE', $qualifiedModuleName);
		$viewer->view('Step1.tpl', $qualifiedModuleName);
	}

	public function step2(Vtiger_Request $request)
	{
		$viewer = $this->getViewer($request);
		$moduleName = $request->getModule();
		$qualifiedModuleName = $request->getModule(false);

		$recordId = $request->get('record');
		if ($recordId) {
			$pdfModel = Settings_PDF_Record_Model::getInstance($recordId);
			$viewer->assign('RECORDID', $recordId);
			$viewer->assign('MODULE_MODEL', $pdfModel->getModule());
			$viewer->assign('MODE', 'edit');
		} else {
			$pdfModel = Settings_PDF_Record_Model::getCleanInstance($moduleName);
		}

		$fields = $pdfModel->getData();
		foreach($fields as $name => $value) {
			$pdfModel->set($name, $request->get($name));
		}

		$viewer->assign('PDF_MODEL', $pdfModel);
		$viewer->assign('ALL_MODULES', Settings_PDF_Module_Model::getSupportedModules());

		$viewer->assign('MODULE', $moduleName);
		$viewer->assign('QUALIFIED_MODULE', $qualifiedModuleName);
		$viewer->view('Step2.tpl', $qualifiedModuleName);
	}

	public function step3(Vtiger_Request $request)
	{
		$viewer = $this->getViewer($request);
		$moduleName = $request->getModule();
		$qualifiedModuleName = $request->getModule(false);

		$recordId = $request->get('record');
		if ($recordId) {
			$pdfModel = Settings_PDF_Record_Model::getInstance($recordId);
			$viewer->assign('RECORDID', $recordId);
			$viewer->assign('MODULE_MODEL', $pdfModel->getModule());
			$viewer->assign('MODE', 'edit');
		} else {
			$pdfModel = Settings_PDF_Record_Model::getCleanInstance($moduleName);
		}

		$fields = $pdfModel->getData();
		foreach($fields as $name => $value) {
			$pdfModel->set($name, $request->get($name));
		}

		$viewer->assign('PDF_MODEL', $pdfModel);
		$viewer->assign('ALL_MODULES', Settings_PDF_Module_Model::getSupportedModules());

		$viewer->assign('MODULE', $moduleName);
		$viewer->assign('QUALIFIED_MODULE', $qualifiedModuleName);
		$viewer->view('Step3.tpl', $qualifiedModuleName);
	}

	public function step4(Vtiger_Request $request)
	{
		$viewer = $this->getViewer($request);
		$moduleName = $request->getModule();
		$qualifiedModuleName = $request->getModule(false);

		$recordId = $request->get('record');
		if ($recordId) {
			$pdfModel = Settings_PDF_Record_Model::getInstance($recordId);
			$viewer->assign('RECORDID', $recordId);
			$viewer->assign('MODULE_MODEL', $pdfModel->getModule());
			$viewer->assign('MODE', 'edit');
		} else {
			$pdfModel = Settings_PDF_Record_Model::getCleanInstance($moduleName);
		}

		$fields = $pdfModel->getData();
		foreach($fields as $name => $value) {
			$pdfModel->set($name, $request->get($name));
		}

		$viewer->assign('PDF_MODEL', $pdfModel);
		$viewer->assign('ALL_MODULES', Settings_PDF_Module_Model::getSupportedModules());

		$viewer->assign('MODULE', $moduleName);
		$viewer->assign('QUALIFIED_MODULE', $qualifiedModuleName);
		$viewer->view('Step4.tpl', $qualifiedModuleName);
	}

	public function step5(Vtiger_Request $request)
	{
		$viewer = $this->getViewer($request);
		$moduleName = $request->getModule();
		$qualifiedModuleName = $request->getModule(false);

		$recordId = $request->get('record');
		if ($recordId) {
			$pdfModel = Settings_PDF_Record_Model::getInstance($recordId);
			$viewer->assign('RECORDID', $recordId);
			$viewer->assign('MODULE_MODEL', $pdfModel->getModule());
			$viewer->assign('MODE', 'edit');
		} else {
			$pdfModel = Settings_PDF_Record_Model::getCleanInstance($moduleName);
		}

		$fields = $pdfModel->getData();
		foreach($fields as $name => $value) {
			$pdfModel->set($name, $request->get($name));
		}

		$viewer->assign('PDF_MODEL', $pdfModel);
		$viewer->assign('ALL_MODULES', Settings_PDF_Module_Model::getSupportedModules());

		$viewer->assign('MODULE', $moduleName);
		$viewer->assign('QUALIFIED_MODULE', $qualifiedModuleName);
		$viewer->view('Step5.tpl', $qualifiedModuleName);
	}

	public function step6(Vtiger_Request $request)
	{
		$viewer = $this->getViewer($request);
		$moduleName = $request->getModule();
		$qualifiedModuleName = $request->getModule(false);

		$recordId = $request->get('record');
		if ($recordId) {
			$pdfModel = Settings_PDF_Record_Model::getInstance($recordId);
			$viewer->assign('RECORDID', $recordId);
			$viewer->assign('MODULE_MODEL', $pdfModel->getModule());
			$viewer->assign('MODE', 'edit');
		} else {
			$pdfModel = Settings_PDF_Record_Model::getCleanInstance($moduleName);
		}

		$fields = $pdfModel->getData();
		foreach($fields as $name => $value) {
			$pdfModel->set($name, $request->get($name));
		}

		$viewer->assign('PDF_MODEL', $pdfModel);
		$viewer->assign('ALL_MODULES', Settings_PDF_Module_Model::getSupportedModules());

		$viewer->assign('MODULE', $moduleName);
		$viewer->assign('QUALIFIED_MODULE', $qualifiedModuleName);
		$viewer->view('Step6.tpl', $qualifiedModuleName);
	}

	public function step7(Vtiger_Request $request)
	{
		$viewer = $this->getViewer($request);
		$moduleName = $request->getModule();
		$qualifiedModuleName = $request->getModule(false);

		$recordId = $request->get('record');
		if ($recordId) {
			$pdfModel = Settings_PDF_Record_Model::getInstance($recordId);
			$viewer->assign('RECORDID', $recordId);
			$viewer->assign('MODULE_MODEL', $pdfModel->getModule());
			$viewer->assign('MODE', 'edit');
		} else {
			$pdfModel = Settings_PDF_Record_Model::getCleanInstance($moduleName);
		}

		$fields = $pdfModel->getData();
		foreach($fields as $name => $value) {
			$pdfModel->set($name, $request->get($name));
		}

		$viewer->assign('PDF_MODEL', $pdfModel);
		$viewer->assign('ALL_MODULES', Settings_PDF_Module_Model::getSupportedModules());

		$viewer->assign('MODULE', $moduleName);
		$viewer->assign('QUALIFIED_MODULE', $qualifiedModuleName);
		$viewer->view('Step7.tpl', $qualifiedModuleName);
	}

	function Step8(Vtiger_Request $request)
	{
		$viewer = $this->getViewer($request);
		$moduleName = $request->getModule();
		$qualifiedModuleName = $request->getModule(false);

		$recordId = $request->get('record');
		if ($recordId) {
			$pdfModel = Settings_PDF_Record_Model::getInstance($recordId);
			$selectedModule = $pdfModel->getModule();
			$selectedModuleName = $selectedModule->getName();
		} else {
			$selectedModuleName = $request->get('module_name');
			$selectedModule = Vtiger_Module_Model::getInstance($selectedModuleName);
			$pdfModel = Settings_PDF_Record_Model::getCleanInstance($selectedModuleName);
		}

		$fields = $pdfModel->getData();
		foreach($fields as $name => $value) {
			$pdfModel->set($name, $request->get($name));
		}

		$viewer->assign('SOURCE_MODULE', $selectedModuleName);
		$viewer->assign('RECORD', $recordId);
		$viewer->assign('MODULE', $moduleName);
		$viewer->assign('PDF_MODEL', $pdfModel);
		$viewer->assign('QUALIFIED_MODULE', $qualifiedModuleName);

		$viewer->view('Step8.tpl', $qualifiedModuleName);
	}

	public function getFooterScripts(Vtiger_Request $request)
	{
		$headerScriptInstances = parent::getFooterScripts($request);
		$moduleName = $request->getModule();

		$jsFileNames = array(
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
			"~layouts/vlayout/modules/Settings/$moduleName/Index.css",
		);
		$cssInstances = $this->checkAndConvertCssStyles($cssFileNames);
		$headerCssInstances = array_merge($cssInstances, $headerCssInstances);
		return $headerCssInstances;
	}
}
