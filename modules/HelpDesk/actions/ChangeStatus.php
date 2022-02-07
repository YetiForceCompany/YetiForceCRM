<?php
/**
 * ChangeStatus action class.
 *
 * @package Action
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Arkadiusz Dudek <a.dudek@yetiforce.com>
 */
class HelpDesk_ChangeStatus_Action extends \App\Controller\Action
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
		if (!\App\Privilege::isPermitted($request->getModule(), 'EditView', $request->getInteger('record'))) {
			throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED', 406);
		}
	}

	/** {@inheritdoc} */
	public function process(App\Request $request)
	{
		$moduleName = $request->getModule();
		$instance = \Vtiger_Module_Model::getInstance($moduleName);
		$instance->massUpdateStatus($request->getInteger('record'), $request->getByType('recordsType', 'Alnum'), $request->getByType('status', 'Text'));
		$response = new Vtiger_Response();
		$response->setResult(['success' => 'true', 'data' => \App\Language::translate('LBL_MASS_STATUS_UPDATED', $moduleName)]);
		$response->emit();
	}
}
