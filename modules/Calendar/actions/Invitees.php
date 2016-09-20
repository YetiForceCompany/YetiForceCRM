<?php
/* {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} */

class Calendar_Invitees_Action extends Vtiger_Action_Controller
{

	public function checkPermission(Vtiger_Request $request)
	{
		$moduleName = $request->getModule();
		$userPrivilegesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		if (!$userPrivilegesModel->hasModulePermission($moduleName)) {
			throw new \Exception\NoPermitted('LBL_PERMISSION_DENIED');
		}
	}

	public function __construct()
	{
		parent::__construct();
		$this->exposeMethod('find');
	}

	public function process(Vtiger_Request $request)
	{
		$mode = $request->getMode();

		if ($mode) {
			$this->invokeExposedMethod($mode, $request);
		}
	}

	public function find(Vtiger_Request $request)
	{
		$value = $request->get('value');
		$modules = array_keys(Vtiger_ModulesHierarchy_Model::getModulesByLevel(0));
		if (empty($modules)) {
			return [];
		}
		$rows = \includes\Record::findCrmidByLabel($value, $modules);

		$matchingRecords = $leadIdsList = [];
		foreach ($rows as &$row) {
			if ($row['moduleName'] === 'Leads') {
				$leadIdsList[] = $row['crmid'];
			}
		}
		$convertedInfo = Leads_Module_Model::getConvertedInfo($leadIdsList);
		foreach ($rows as &$row) {
			if ($row['moduleName'] === 'Leads' && $convertedInfo[$row['crmid']]) {
				continue;
			}
			if (Users_Privileges_Model::isPermitted($row['moduleName'], 'DetailView', $row['crmid'])) {
				$label = \includes\Record::getLabel($row['crmid']);
				$matchingRecords[] = [
					'id' => $row['crmid'],
					'module' => $row['moduleName'],
					'category' => vtranslate($row['moduleName'], $row['moduleName']),
					'fullLabel' => vtranslate($row['moduleName'], $row['moduleName']) . ': ' . $label,
					'label' => $label
				];
			}
		}
		$response = new Vtiger_Response();
		$response->setResult($matchingRecords);
		$response->emit();
	}
}
