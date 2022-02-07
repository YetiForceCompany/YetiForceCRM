<?php

/**
 * Mass records delete action class.
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Arkadiusz Dudek <a.dudek@yetiforce.com>
 */

/**
 * Mass delete all action class.
 */
class RecycleBin_MassDeleteAll_Action extends Vtiger_Mass_Action
{
	/** {@inheritdoc} */
	public function checkPermission(App\Request $request)
	{
		if (!\App\Privilege::isPermitted($request->getModule())) {
			throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED', 406);
		}
	}

	/** {@inheritdoc} */
	public function process(App\Request $request)
	{
		$result = (new App\BatchMethod(['method' => 'RecycleBin_Module_Model::deleteAllRecords', 'params' => [date('Y-m-d H:i:s'), App\User::getCurrentUserId()]]))->save();
		$response = new Vtiger_Response();
		$response->setResult($result);
		$response->emit();
	}
}
