<?php

/**
 * Export PDF Modal View Class for PDF Settings
 * @package YetiForce.View
 * @license licenses/License.html
 * @author Maciej Stencel <m.stencel@yetiforce.com>
 */
class Settings_PDF_ExportPDF_View extends Vtiger_BasicModal_View
{

	public function checkPermission(Vtiger_Request $request)
	{
		return true;
		//TODO permissions
//		$moduleName = $request->getModule();
//		if (!Users_Privileges_Model::isPermitted($moduleName, $actionName)) {
//			throw new AppException(vtranslate('LBL_PERMISSION_DENIED'));
//		}
	}

	public function preProcess(Vtiger_Request $request)
	{
		parent::preProcess($request);
		foreach ($this->getModalCss($request) as &$style) {
			echo '<link rel="stylesheet" href="'.$style->getHref().'">';
		}
	}

	function process(Vtiger_Request $request)
	{
		$this->preProcess($request);
		$moduleName	= $request->getModule(false);
		$viewer		= $this->getViewer($request);
		$recordId	= $request->get('record');
		$fromModule	= $request->get('frommodule');
		$view		= $request->get('fromview');

		$pdfModuleModel = Settings_PDF_Module_Model::getInstance('Settings:PDF');

		if ($view === 'Detail') {
			$viewer->assign('TEMPLATES', $pdfModuleModel->getTemplatesForRecordId($recordId, $view, $fromModule));
		} elseif ($view === 'List') {
			$templates = $pdfModuleModel->getTemplatesByModule($fromModule);
			// check template visibility
			$pdfModuleModel->removeInvisibleTemplates($templates, $view);
			$pdfModuleModel->removeFailingPermissionTemplates($templates);

			$viewer->assign('TEMPLATES', $templates);
		}
		$exportValues = "&record={$recordId}&frommodule={$fromModule}&fromview={$view}";
		$viewer->assign('EXPORT_VARS', $exportValues);
		$viewer->assign('QUALIFIED_MODULE', $moduleName);
		$viewer->view('ExportPDF.tpl', $moduleName);
		$this->postProcess($request);
	}

	public function getModalScripts(Vtiger_Request $request)
	{
		$moduleName = $request->getModule();
		$viewName = $request->get('view');

		$scripts = array(
			"modules.Settings.$moduleName.resources.$viewName"
		);

		$scriptInstances = $this->checkAndConvertJsScripts($scripts);
		return $scriptInstances;
	}

	//TODO add functionality to get css style files to modals in general
	function getModalCss(Vtiger_Request $request)
	{
		$moduleName = $request->getModule();
		$viewName = $request->get('view');

		$cssFileNames = array(
			"~layouts/vlayout/modules/Settings/$moduleName/$viewName.css",
			"~layouts/vlayout/modules/$moduleName/$viewName.css"
		);

		$cssInstances = $this->checkAndConvertCssStyles($cssFileNames);
		$headerCssInstances = $cssInstances;
		return $headerCssInstances;
	}
}
