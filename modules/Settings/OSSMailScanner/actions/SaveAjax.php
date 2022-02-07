<?php

/**
 * Mail scanner action creating mail.
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Settings_OSSMailScanner_SaveAjax_Action extends Settings_Vtiger_Basic_Action
{
	public function __construct()
	{
		parent::__construct();
		$this->exposeMethod('updateFolders');
	}

	public function updateFolders(App\Request $request)
	{
		$user = $request->getInteger('user');
		$folders = $request->getArray('folders', 'Text');
		$mailScannerRecordModel = Vtiger_Record_Model::getCleanInstance('OSSMailScanner');
		$mailScannerRecordModel->setFolderList($user, $folders);
		$response = new Vtiger_Response();
		$response->setResult([
			'success' => true,
			'message' => \App\Language::translate('LBL_SAVE_FOLDER_INFO', $request->getModule()),
		]);
		$response->emit();
	}
}
