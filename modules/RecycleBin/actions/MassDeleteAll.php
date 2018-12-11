<?php

/**
 * Mass records delete action class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Arkadiusz Dudek <a.dudek@yetiforce.com>
 */
class RecycleBin_MassDeleteAll_Action extends Vtiger_Mass_Action
{
	/**
	 * {@inheritdoc}
	 */
	public function checkPermission(\App\Request $request)
	{
		$userPriviligesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		if (!$userPriviligesModel->hasModuleActionPermission($request->getByType('sourceModule', 2), 'MassDelete')) {
			throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED', 406);
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function process(\App\Request $request)
	{
		(new App\BatchMethod(['method' => 'RecycleBin_Module_Model::deleteAllRecords', 'params' => DateTimeField::convertToDBFormat(date('Y-m-d H:i:s'))]))->save();
		$response = new Vtiger_Response();
		$response->setResult(['notify' => ['text' => App\Language::translate('LBL_ADDED_TO_QUEUE', $request->getModule()), 'type' => 'success']]);
		$response->emit();
	}
}
