<?php
/**
 * ServiceContracts detail view class.
 *
 * @package View
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class ServiceContracts_Detail_View extends Vtiger_Detail_View
{
	/**
	 * {@inheritdoc}
	 */
	public function __construct()
	{
		parent::__construct();
		$this->exposeMethod('showSlaPolicyView');
	}

	/**
	 * Show SLA Policy.
	 *
	 * @param \App\Request $request
	 *
	 * @throws \App\Exceptions\IllegalValue
	 *
	 * @return string
	 */
	public function showSlaPolicyView(App\Request $request)
	{
		$moduleName = $request->getModule();
		$relatedModuleName = $request->getByType('target', 'Alnum');
		$rows = \App\Utils\ServiceContracts::getSlaPolicyForServiceContracts($request->getInteger('record'), \App\Module::getModuleId($relatedModuleName));
		$policyType = 0;
		if (isset($rows[0])) {
			$policyType = (int) $rows[0]['policy_type'];
		}
		$viewer = $this->getViewer($request);
		$viewer->assign('ALL_BUSINESS_HOURS', \App\Utils\ServiceContracts::getAllBusinessHours());
		$viewer->assign('SLA_POLICY_ROWS', $rows);
		$viewer->assign('POLICY_TYPE', $policyType);
		$viewer->assign('MODULE', $moduleName);
		$viewer->assign('TARGET_MODULE', $relatedModuleName);
		$viewer->assign('SOURCE_MODULE', $relatedModuleName);
		$recordStructureModulesField = [];
		$moduleModel = Vtiger_Module_Model::getInstance($relatedModuleName);
		foreach ($moduleModel->getFieldsByReference() as $referenceField) {
			foreach ($referenceField->getReferenceList() as $relatedModuleName) {
				$recordStructureModulesField[$relatedModuleName][$referenceField->getFieldName()] = Vtiger_RecordStructure_Model::getInstanceForModule(Vtiger_Module_Model::getInstance($relatedModuleName))->getStructure();
			}
		}
		$viewer->assign('RECORD_STRUCTURE_RELATED_MODULES', $recordStructureModulesField);
		$viewer->assign('RECORD_STRUCTURE', Vtiger_RecordStructure_Model::getInstanceForModule($moduleModel)->getStructure());
		$viewer->assign('VIEW', $request->getByType('view'));
		return $viewer->view('SlaPolicy.tpl', $moduleName, true);
	}

	/**
	 * {@inheritdoc}
	 */
	public function getFooterScripts(App\Request $request)
	{
		return array_merge(
			parent::getFooterScripts($request),
			$this->checkAndConvertJsScripts([
				'modules.ServiceContracts.resources.InRelation'
			])
		);
	}
}
