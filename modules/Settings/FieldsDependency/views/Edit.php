<?php
/**
 * Settings fields dependency edit view file.
 *
 * @package   Settings.View
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

/**
 * Settings fields dependency edit view class.
 */
class Settings_FieldsDependency_Edit_View extends Settings_Vtiger_Index_View
{
	use \App\Controller\ExposeMethod;
	use \App\Controller\Traits\SettingsPermission;

	/** {@inheritdoc} */
	public function __construct()
	{
		parent::__construct();
		$this->exposeMethod('dynamic');
	}

	/** {@inheritdoc} */
	public function process(App\Request $request)
	{
		if ($mode = $request->getMode()) {
			$this->invokeExposedMethod($mode, $request);
		} else {
			$viewer = $this->getViewer($request);
			$qualifiedModuleName = $request->getModule(false);
			$modules = \vtlib\Functions::getAllModules(true, true, 0);
			if ($request->isEmpty('record')) {
				$recordModel = Settings_FieldsDependency_Record_Model::getCleanInstance();
				if ($firstModule = current($modules)) {
					$selectedModuleName = $firstModule['name'];
					$recordModel->set('tabid', $firstModule['tabid']);
				}
			} else {
				$viewer->assign('RECORD_ID', $request->getInteger('record'));
				$recordModel = Settings_FieldsDependency_Record_Model::getInstanceById($request->getInteger('record'));
				$selectedModuleName = \App\Module::getModuleName($recordModel->get('tabid'));
			}
			$viewer->assign('MODULES', $modules);
			$viewer->assign('FIELDS', \App\Json::decode($recordModel->get('fields')) ?: []);
			$viewer->assign('VIEWS', \App\Json::decode($recordModel->get('views')) ?: []);
			if (isset($selectedModuleName)) {
				$sourceModuleModel = Vtiger_Module_Model::getInstance($selectedModuleName);
				$recordStructureModulesField = [];
				foreach ($sourceModuleModel->getFieldsByReference() as $referenceField) {
					foreach ($referenceField->getReferenceList() as $relatedModuleName) {
						$recordStructureModulesField[$relatedModuleName][$referenceField->getFieldName()] = Vtiger_RecordStructure_Model::getInstanceForModule(Vtiger_Module_Model::getInstance($relatedModuleName))->getStructure();
					}
				}
				$viewer->assign('ADVANCE_CRITERIA', \App\Json::decode($recordModel->get('conditions')));
				$viewer->assign('SOURCE_MODULE', $selectedModuleName);
				$viewer->assign('RECORD_STRUCTURE_RELATED_MODULES', $recordStructureModulesField);
				$viewer->assign('RECORD_STRUCTURE', Vtiger_RecordStructure_Model::getInstanceForModule($sourceModuleModel)->getStructure());
			}
			$viewer->assign('RECORD', $recordModel);
			$viewer->assign('MODULE', $request->getModule());
			$viewer->assign('QUALIFIED_MODULE', $qualifiedModuleName);
			$viewer->view('EditView.tpl', $qualifiedModuleName);
		}
	}

	/**
	 * Dynamic view.
	 *
	 * @param App\Request $request
	 *
	 * @return void
	 */
	public function dynamic(App\Request $request): void
	{
		$viewer = $this->getViewer($request);
		$qualifiedModuleName = $request->getModule(false);

		$selectedModuleName = $request->getByType('selectedModule', 'Alnum');
		$sourceModuleModel = Vtiger_Module_Model::getInstance($selectedModuleName);
		$recordStructureModulesField = [];
		foreach ($sourceModuleModel->getFieldsByReference() as $referenceField) {
			foreach ($referenceField->getReferenceList() as $relatedModuleName) {
				foreach ($referenceField->getReferenceList() as $relatedModuleName) {
					$recordStructureModulesField[$relatedModuleName][$referenceField->getFieldName()] = Vtiger_RecordStructure_Model::getInstanceForModule(Vtiger_Module_Model::getInstance($relatedModuleName))->getStructure();
				}
			}
		}
		$viewer->assign('FIELDS', []);
		$viewer->assign('VIEWS', []);
		$viewer->assign('ADVANCE_CRITERIA', '[]');
		$viewer->assign('SOURCE_MODULE', $selectedModuleName);
		$viewer->assign('RECORD_STRUCTURE_RELATED_MODULES', $recordStructureModulesField);
		$viewer->assign('RECORD_STRUCTURE', Vtiger_RecordStructure_Model::getInstanceForModule($sourceModuleModel)->getStructure());
		$viewer->assign('MODULE', $request->getModule());
		$viewer->assign('QUALIFIED_MODULE', $qualifiedModuleName);
		$viewer->view('DynamicBlocks.tpl', $qualifiedModuleName);
	}
}
