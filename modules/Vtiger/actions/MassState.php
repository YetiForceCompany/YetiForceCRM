<?php

/**
 * Mass records state action class.
 *
 * @package Action
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Vtiger_MassState_Action extends Vtiger_Mass_Action
{
	/**
	 * Function to check permission.
	 *
	 * @param \App\Request $request
	 *
	 * @throws \App\Exceptions\NoPermitted
	 */
	public function checkPermission(App\Request $request)
	{
		$userPriviligesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		switch ($request->getByType('state')) {
			case 'Archived':
				if ($userPriviligesModel->hasModuleActionPermission($request->getModule(), 'MassArchived')) {
					return true;
				}
				break;
			case 'Trash':
				if ($userPriviligesModel->hasModuleActionPermission($request->getModule(), 'MassTrash')) {
					return true;
				}
				break;
			case 'Active':
				if ($userPriviligesModel->hasModuleActionPermission($request->getModule(), 'MassActive')) {
					return true;
				}
				break;
			default:
				break;
		}
		throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED', 406);
	}

	/**
	 * Process.
	 *
	 * @param \App\Request $request
	 *
	 * @throws \App\Exceptions\AppException
	 */
	public function process(App\Request $request)
	{
		$moduleName = $request->getModule();
		$recordIds = self::getRecordsListFromRequest($request);
		$skipped = [];
		foreach ($recordIds as $recordId) {
			$recordModel = Vtiger_Record_Model::getInstanceById($recordId, $moduleName);
			switch ($request->getByType('state')) {
				case 'Archived':
					if (!$recordModel->privilegeToArchive()) {
						$skipped[] = $recordModel->getName();
						continue 2;
					}
					break;
				case 'Trash':
					if (!$recordModel->privilegeToMoveToTrash()) {
						$skipped[] = $recordModel->getName();
						continue 2;
					}
					break;
				case 'Active':
					if (!$recordModel->privilegeToActivate()) {
						$skipped[] = $recordModel->getName();
						continue 2;
					}
					break;
				default:
					break;
			}
			$recordModel->changeState($request->getByType('state'));
			unset($recordModel);
		}
		$text = \App\Language::translate('LBL_CHANGES_SAVED');
		$type = 'success';
		if ($skipped) {
			$type = 'info';
			$text .= PHP_EOL . \App\Language::translate('LBL_OMITTED_RECORDS');
			foreach ($skipped as $name) {
				$text .= PHP_EOL . $name;
			}
		}
		$response = new Vtiger_Response();
		$response->setResult(['notify' => ['text' => $text, 'type' => $type]]);
		$response->emit();
	}
}
