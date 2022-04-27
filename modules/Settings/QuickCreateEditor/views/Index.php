<?php

/**
 * Settings QuickCreateEditor index view class.
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 * @author    Adrian Kon <a.kon@yetiforce.com>
 */
class Settings_QuickCreateEditor_Index_View extends Settings_Vtiger_Index_View
{
	use \App\Controller\ExposeMethod;

	public function __construct()
	{
		parent::__construct();
		$this->exposeMethod('showFieldLayout');
	}

	/**
	 * Process.
	 *
	 * @param \App\Request $request
	 */
	public function process(App\Request $request)
	{
		$mode = $request->getMode();
		if ($this->isMethodExposed($mode)) {
			$this->invokeExposedMethod($mode, $request);
		} else {
			$this->showFieldLayout($request);
		}
	}

	/**
	 * View.
	 *
	 * @param \App\Request $request
	 */
	public function showFieldLayout(App\Request $request)
	{
		$sourceModule = $request->getByType('sourceModule', 2);
		$menuModelsList = \App\Module::getQuickCreateModules();

		if (empty($sourceModule)) {
			$firstElement = reset($menuModelsList);
			$sourceModule = $firstElement->get('name');
		}
		$recordModel = Vtiger_Record_Model::getCleanInstance($sourceModule);
		$quickCreateFields = Vtiger_RecordStructure_Model::getInstanceFromRecordModel($recordModel, Vtiger_RecordStructure_Model::RECORD_STRUCTURE_MODE_QUICKCREATE)->getStructure();
		$viewer = $this->getViewer($request);
		$viewer->assign('RECORD_STRUCTURE', $quickCreateFields);
		$layout = $recordModel->getModule()->getLayoutTypeForQuickCreate();
		if ('blocks' === $layout) {
			$selectedModuleModel = Settings_LayoutEditor_Module_Model::getInstanceByName($sourceModule);
			$blockModels = $selectedModuleModel->getBlocks();
			$blockIdFieldMap = [];
			foreach ($quickCreateFields as $fieldModel) {
				$blockIdFieldMap[$fieldModel->getBlockId()][$fieldModel->getName()] = $fieldModel;
			}
			foreach ($blockModels as $blockKey => $blockModel) {
				if (isset($blockIdFieldMap[$blockModel->get('id')])) {
					$fieldModelList = $blockIdFieldMap[$blockModel->get('id')];
					$blockModel->setFields($fieldModelList);
				} else {
					unset($blockModels[$blockKey]);
				}
			}
			$viewer->assign('BLOCKS', $blockModels);
			$viewer->assign('SELECTED_MODULE_MODEL', $selectedModuleModel);
		}
		$viewer->assign('LAYOUT', $layout);
		$viewer->assign('SELECTED_MODULE_NAME', $sourceModule);
		$viewer->assign('SUPPORTED_MODULES', $menuModelsList);
		$viewer->view('Index.tpl', $request->getModule(false));
	}
}
