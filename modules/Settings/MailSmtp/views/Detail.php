<?php

/**
 * Mail edit view.
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author  Adrian Koń <a.kon@yetiforce.com>
 * @author  Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Settings_MailSmtp_Detail_View extends Settings_Vtiger_Index_View
{
	/** {@inheritdoc} */
	public function checkPermission(App\Request $request)
	{
		parent::checkPermission($request);
		if ($request->isEmpty('record')) {
			throw new \App\Exceptions\NoPermittedForAdmin('LBL_PERMISSION_DENIED');
		}
	}

	/** {@inheritdoc} */
	public function process(App\Request $request)
	{
		$record = $request->getInteger('record');
		$qualifiedModuleName = $request->getModule(false);
		$recordModel = Settings_MailSmtp_Record_Model::getInstanceById($record);

		$viewer = $this->getViewer($request);
		$viewer->assign('RECORD_MODEL', $recordModel);
		$viewer->assign('QUALIFIED_MODULE', $qualifiedModuleName);
		$viewer->view('DetailView.tpl', $qualifiedModuleName);
	}
}
