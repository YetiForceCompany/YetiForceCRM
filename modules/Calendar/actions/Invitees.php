<?php

/**
 * Calendar invitees action class
 * @package YetiForce.Action
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 2.0 (licenses/License.html or yetiforce.com)
 */
class Calendar_Invitees_Action extends Vtiger_Action_Controller
{

	public function checkPermission(\App\Request $request)
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

	public function process(\App\Request $request)
	{
		$mode = $request->getMode();

		if ($mode) {
			$this->invokeExposedMethod($mode, $request);
		}
	}

	public function find(\App\Request $request)
	{
		$value = $request->get('value');
		$modules = array_keys(\App\ModuleHierarchy::getModulesByLevel(0));
		if (empty($modules)) {
			return [];
		}
		$rows = (new \App\RecordSearch($value, $modules, 10))->search();

		$matchingRecords = $leadIdsList = [];
		foreach ($rows as &$row) {
			if ($row['setype'] === 'Leads') {
				$leadIdsList[] = $row['crmid'];
			}
		}
		$convertedInfo = Leads_Module_Model::getConvertedInfo($leadIdsList);
		foreach ($rows as &$row) {
			if ($row['setype'] === 'Leads' && $convertedInfo[$row['crmid']]) {
				continue;
			}
			if (Users_Privileges_Model::isPermitted($row['moduleName'], 'DetailView', $row['crmid'])) {
				$label = \App\Record::getLabel($row['crmid']);
				$matchingRecords[] = [
					'id' => $row['crmid'],
					'module' => $row['setype'],
					'category' => \App\Language::translate($row['setype'], $row['setype']),
					'fullLabel' => \App\Language::translate($row['setype'], $row['setype']) . ': ' . $label,
					'label' => $label
				];
			}
		}
		$response = new Vtiger_Response();
		$response->setResult($matchingRecords);
		$response->emit();
	}
}
