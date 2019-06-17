<?php
/*
 * SlaPolicy CustomAjax Action class
 *
 * @package   Action
 *
 * @copyright YetiForce Sp. z o.o.
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Rafal Pospiech <r.pospiech@yetiforce.com>
*/
class SlaPolicy_CustomAjax_Action extends \App\Controller\Action
{
	/**
	 * {@inheritdoc}
	 */
	public function checkPermission(App\Request $request)
	{
		$currentUserPrivilegesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		if (!$currentUserPrivilegesModel->hasModulePermission($request->getByType('targetModule', 'Alnum')) || !$currentUserPrivilegesModel->hasModulePermission($request->getModule())) {
			throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED', 406);
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function process(App\Request $request)
	{
		$recordModels = Settings_SlaPolicy_Record_Model::getForModule($request->getByType('targetModule', 'Alnum'));
		$rows = [];
		foreach ($recordModels as $recordModel) {
			$row = [];
			foreach ($recordModel->getKeys() as $fieldName) {
				$row[$fieldName] = $recordModel->getDisplayValue($fieldName);
			}
			$rows[] = $row;
		}
		$response = new Vtiger_Response();
		$response->setResult($rows);
		$response->emit();
	}
}
