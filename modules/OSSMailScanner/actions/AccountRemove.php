<?php

/**
 * OSSMailScanner AccountRemove action class.
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
class OSSMailScanner_AccountRemove_Action extends \App\Controller\Action
{
	/**
	 * Function to check permission.
	 *
	 * @param \App\Request $request
	 *
	 * @throws \App\Exceptions\NoPermittedForAdmin
	 */
	public function checkPermission(App\Request $request)
	{
		$currentUserModel = Users_Record_Model::getCurrentUserModel();
		if (!$currentUserModel->isAdminUser()) {
			throw new \App\Exceptions\NoPermittedForAdmin('LBL_PERMISSION_DENIED');
		}
	}

	public function process(App\Request $request)
	{
		$id = $request->getInteger('id');
		$recordModelOSSMailScanner = Vtiger_Record_Model::getCleanInstance('OSSMailScanner');
		$recordModelOSSMailScanner->accountDelete($id);
		$response = new Vtiger_Response();
		$response->setResult(['success' => true, 'data' => \App\Language::translate('AccontDeleteOK', 'OSSMailScanner')]);
		$response->emit();
	}
}
