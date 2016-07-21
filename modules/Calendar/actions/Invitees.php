<?php

class Calendar_Invitees_Action extends Vtiger_Action_Controller
{

	public function checkPermission(Vtiger_Request $request)
	{
		$moduleName = $request->getModule();
		$moduleModel = Vtiger_Module_Model::getInstance($moduleName);

		$userPrivilegesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		if (!$userPrivilegesModel->hasModulePermission($moduleModel->getId())) {
			throw new NoPermittedException('LBL_PERMISSION_DENIED');
		}
	}

	function __construct()
	{
		parent::__construct();
		$this->exposeMethod('find');
	}

	function process(Vtiger_Request $request)
	{
		$mode = $request->getMode();

		if ($mode) {
			$this->invokeExposedMethod($mode, $request);
		}
	}

	public function find(Vtiger_Request $request)
	{
		$value = $request->get('value');

		$db = PearDatabase::getInstance();
		$params = ["%$value%", 1];
		$modules = array_keys(Vtiger_ModulesHierarchy_Model::getModulesByLevel(0));
		if (empty($modules)) {
			return [];
		}
		$modules = implode("','", $modules);
		$query = "SELECT vtiger_crmentity_label.label, vtiger_crmentity.crmid, setype 
			FROM vtiger_crmentity 
			INNER JOIN vtiger_entityname ON vtiger_crmentity.setype = vtiger_entityname.modulename 
			INNER JOIN vtiger_crmentity_search ON vtiger_crmentity_search.crmid = vtiger_crmentity.crmid 
			INNER JOIN vtiger_crmentity_label ON vtiger_crmentity_label.crmid = vtiger_crmentity.crmid 
			WHERE vtiger_crmentity.setype IN ('$modules') AND vtiger_crmentity_search.searchlabel LIKE ? 
			AND vtiger_crmentity.deleted = 0 AND vtiger_entityname.turn_off = ?";
		$result = $db->pquery($query, $params);
		$matchingRecords = $rows = $leadIdsList = [];
		while ($row = $db->getRow($result)) {
			if ($row['setype'] === 'Leads') {
				$leadIdsList[] = $row['crmid'];
			}
			$rows[] = $row;
		}
		$convertedInfo = Leads_Module_Model::getConvertedInfo($leadIdsList);
		foreach ($rows as &$row) {
			if ($row['setype'] === 'Leads' && $convertedInfo[$row['crmid']]) {
				continue;
			}
			if (Users_Privileges_Model::isPermitted($row['setype'], 'DetailView', $row['crmid'])) {
				$matchingRecords[] = [
					'id' => $row['crmid'],
					'module' => $row['setype'],
					'category' => vtranslate($row['setype'], $row['setype']),
					'fullLabel' => vtranslate($row['setype'], $row['setype']) . ': ' . $row['label'],
					'label' => $row['label']
				];
			}
		}
		$response = new Vtiger_Response();
		$response->setResult($matchingRecords);
		$response->emit();
	}
}
