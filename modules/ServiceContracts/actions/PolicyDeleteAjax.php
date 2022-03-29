<?php
/**
 * ServiceContracts Policy DeleteAjax Action class.
 *
 * @package   Action
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Rafal Pospiech <r.pospiech@yetiforce.com>
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class ServiceContracts_PolicyDeleteAjax_Action extends \App\Controller\Action
{
	/** {@inheritdoc} */
	public function checkPermission(App\Request $request)
	{
		if ($request->isEmpty('record') || $request->isEmpty('rowId', true)) {
			throw new \App\Exceptions\NoPermittedToRecord('ERR_NO_PERMISSIONS_FOR_THE_RECORD', 406);
		}
		$record = Vtiger_Record_Model::getInstanceById($request->getInteger('record'), $request->getModule());
		$userPrivilegesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		if (!$record->isViewable() || !$userPrivilegesModel->hasModuleActionPermission($record->getModuleName(), 'ServiceContractsSla') || !$userPrivilegesModel->hasModulePermission($request->getByType('targetModule', \App\Purifier::ALNUM))) {
			throw new \App\Exceptions\NoPermittedToRecord('ERR_NO_PERMISSIONS_FOR_THE_RECORD', 406);
		}
	}

	/** {@inheritdoc} */
	public function process(App\Request $request)
	{
		\App\Utils\ServiceContracts::deleteSlaPolicy($request->getInteger('record'), \App\Module::getModuleId($request->getByType('targetModule', 'Alnum')), $request->getInteger('rowId'));
		$response = new Vtiger_Response();
		$response->setResult(true);
		$response->emit();
	}
}
