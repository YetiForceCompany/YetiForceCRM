<?php
/**
 * SlaPolicy_Delete_Action class.
 *
 * @package   Action
 *
 * @copyright YetiForce Sp. z o.o.
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Rafal Pospiech <r.pospiech@yetiforce.com>
 */
class SlaPolicy_DeleteAjax_Action extends \App\Controller\Action
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
		\App\Db::getInstance()->createCommand()->delete('u_#__servicecontracts_sla_policy', ['id' => $request->getInteger('record')])->execute();
		$response = new Vtiger_Response();
		$response->setResult(true);
		$response->emit();
	}
}
