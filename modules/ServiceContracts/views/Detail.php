<?php
/**
 * Service contracts detail view  file.
 *
 * @package View
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

/**
 * Service contracts detail view  class.
 */
class ServiceContracts_Detail_View extends Vtiger_Detail_View
{
	/** {@inheritdoc} */
	public function __construct()
	{
		parent::__construct();
		$this->exposeMethod('showSlaPolicyView');
	}

	/** {@inheritdoc} */
	public function checkPermission(App\Request $request)
	{
		parent::checkPermission($request);
		if ('showSlaPolicyView' === $request->getMode()) {
			$userPrivilegesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
			if (!$userPrivilegesModel->hasModuleActionPermission($this->record->getModuleName(), 'ServiceContractsSla') || !$userPrivilegesModel->hasModulePermission($request->getByType('target', \App\Purifier::ALNUM))) {
				throw new \App\Exceptions\NoPermittedToRecord('ERR_NO_PERMISSIONS_FOR_THE_RECORD', 406);
			}
		}
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
		$relatedModuleName = $request->getByType('target', \App\Purifier::ALNUM);
		$rows = \App\Utils\ServiceContracts::getSlaPolicyForServiceContracts($request->getInteger('record'), \App\Module::getModuleId($relatedModuleName));
		$policyType = 0;
		if (isset($rows[0])) {
			$policyType = (int) $rows[0]['policy_type'];
		}
		$viewer = $this->getViewer($request);
		$viewer->assign('RECORD', $this->record->getRecord());
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

	/** {@inheritdoc} */
	public function getFooterScripts(App\Request $request)
	{
		return array_merge(
			parent::getFooterScripts($request),
			$this->checkAndConvertJsScripts([
				'modules.ServiceContracts.resources.InRelation',
			])
		);
	}
}
