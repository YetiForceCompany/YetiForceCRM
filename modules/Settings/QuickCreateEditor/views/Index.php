<?php

/**
 * Settings QuickCreateEditor index view class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 * @author    Adrian Kon <a.kon@yetiforce.com>
 */
class Settings_QuickCreateEditor_Index_View extends Settings_Vtiger_Index_View
{
	use \App\Controller\ExposeMethod;

	public function __construct()
	{
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
		$menuModelsList = Vtiger_Module_Model::getQuickCreateModules();

		if (empty($sourceModule)) {
			$firstElement = reset($menuModelsList);
			$sourceModule = $firstElement->get('name');
		}

		$quickCreateFields = Vtiger_RecordStructure_Model::getInstanceFromRecordModel(Vtiger_Record_Model::getCleanInstance($sourceModule), Vtiger_RecordStructure_Model::RECORD_STRUCTURE_MODE_QUICKCREATE)->getStructure();
		$viewer = $this->getViewer($request);
		$displayBlocks = App\Config::performance('showBlocksInQuickCreate');
		if ($displayBlocks) {
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
		} else {
			$viewer->assign('RECORD_STRUCTURE', $quickCreateFields);
		}

		$qualifiedModule = $request->getModule(false);
		$viewer->assign('DISPLAY_BLOCKS', \App\Config::performance('showBlocksInQuickCreate'));
		$viewer->assign('SELECTED_MODULE_NAME', $sourceModule);
		$viewer->assign('SUPPORTED_MODULES', $menuModelsList);
		$viewer->assign('QUALIFIED_MODULE', $qualifiedModule);
		$viewer->view('Index.tpl', $qualifiedModule);
	}
}
