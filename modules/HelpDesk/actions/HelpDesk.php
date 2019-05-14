<?php
/**
 * HelpDesk action class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Arkadiusz Dudek <a.dudek@yetiforce.com>
 */
class HelpDesk_HelpDesk_Action extends \App\Controller\Action
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
		$moduleName = $request->getModule();
		if (!\App\Privilege::isPermitted($moduleName, 'EditView')) {
			throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED', 406);
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function process(App\Request $request)
	{
		$moduleName = $request->getModule();
		$recordId = $request->getInteger('record');
		$recordsType = $request->getByType('recordsType', 'Alnum');
		$status = $request->getByType('status', 'Text');
		$instance = CRMEntity::getInstance($moduleName);
		$instance->massUpdateStatus($recordId, $recordsType, $status);
		$response = new Vtiger_Response();
		$response->setResult(['success' => 'true', 'data' => \App\Language::translate('LBL_MASS_STATUS_UPDATED', $moduleName)]);
		$response->emit();
	}
}
