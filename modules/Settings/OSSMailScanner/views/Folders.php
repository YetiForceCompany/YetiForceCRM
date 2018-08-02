<?php

/**
 * Mail scanner action creating mail.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Settings_OSSMailScanner_Folders_View extends Vtiger_BasicModal_View
{
	/**
	 * Check permission to view.
	 *
	 * @param \App\Request $request
	 *
	 * @throws \App\Exceptions\NoPermittedForAdmin
	 */
	public function checkPermission(\App\Request $request)
	{
		if (!\App\User::getCurrentUserModel()->isAdmin() || !$request->has('record')) {
			throw new \App\Exceptions\NoPermittedForAdmin('LBL_PERMISSION_DENIED');
		}
	}

	public function getSize(\App\Request $request)
	{
		return 'modal-lg';
	}

	/**
	 * Process.
	 *
	 * @param \App\Request $request
	 */
	public function process(\App\Request $request)
	{
		$moduleName = $request->getModule();
		$qualifiedModuleName = $request->getModule(false);
		$record = $request->getInteger('record');
		$mailDetail = OSSMail_Record_Model::getMailAccountDetail($record);
		$mailModuleActive = \App\Module::getModuleId('OSSMail');
		$folders = [];
		if ($mailModuleActive) {
			$mailRecordModel = Vtiger_Record_Model::getCleanInstance('OSSMail');
			$folders = $mailRecordModel->getFolders($record);
			$mailScannerRecordModel = Vtiger_Record_Model::getCleanInstance('OSSMailScanner');
			$mailScannerFolders = $mailScannerRecordModel->getFolders($record);
			$selectedFolders = [];
			$missingFolders = [];
			foreach ($mailScannerFolders as &$folder) {
				if (!isset($folders[$folder['folder']])) {
					$missingFolders[] = $folder['folder'];
				}
				$selectedFolders[$folder['type']][] = $folder['folder'];
			}
		}

		$this->preProcess($request);
		$viewer = $this->getViewer($request);
		$viewer->assign('RECORD', $record);
		$viewer->assign('FOLDERS', $folders);
		$viewer->assign('SELECTED', $selectedFolders);
		$viewer->assign('MODULE_NAME', $moduleName);
		$viewer->assign('QUALIFIED_MODULE', $qualifiedModuleName);
		$viewer->assign('ADDRESS_EMAIL', $mailDetail['username']);
		$viewer->assign('MISSING_FOLDERS', $missingFolders);
		$viewer->view('Folders.tpl', $qualifiedModuleName);
		$this->postProcess($request);
	}
}
