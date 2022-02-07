<?php

/**
 * Calendar invitees action class.
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
class Calendar_Invitees_Action extends \App\Controller\Action
{
	use \App\Controller\ExposeMethod;

	/**
	 * Function to check permission.
	 *
	 * @param \App\Request $request
	 *
	 * @throws \App\Exceptions\NoPermitted
	 */
	public function checkPermission(App\Request $request)
	{
		$moduleName = $request->getModule();
		$userPrivilegesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		if (!$userPrivilegesModel->hasModulePermission($moduleName)) {
			throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED', 406);
		}
	}

	/**
	 * Construct.
	 */
	public function __construct()
	{
		parent::__construct();
		$this->exposeMethod('find');
	}

	/**
	 * Find a records for invitations in CRM.
	 *
	 * @param \App\Request $request
	 */
	public function find(App\Request $request)
	{
		$value = $request->getByType('value', 'Text');
		$modules = array_keys(array_merge(\App\ModuleHierarchy::getModulesByLevel(4), \App\ModuleHierarchy::getModulesByLevel(0)));
		if (empty($modules)) {
			return [];
		}
		$rows = (new \App\RecordSearch($value, $modules, 10))->search();
		$matchingRecords = [];
		foreach ($rows as $row) {
			if (\App\Privilege::isPermitted($row['setype'], 'DetailView', $row['crmid'])) {
				$label = \App\Record::getLabel($row['crmid']);
				$matchingRecords[] = [
					'id' => $row['crmid'],
					'module' => $row['setype'],
					'category' => \App\Language::translate($row['setype'], $row['setype']),
					'fullLabel' => \App\Language::translate($row['setype'], $row['setype']) . ': ' . $label,
					'label' => $label,
				];
			}
		}
		$response = new Vtiger_Response();
		$response->setResult($matchingRecords);
		$response->emit();
	}
}
