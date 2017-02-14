<?php

/**
 * Mail scanner action creating mail
 * @package YetiForce.View
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Settings_OSSMailScanner_Folders_View extends Vtiger_BasicModal_View
{

	public function checkPermission(Vtiger_Request $request)
	{
		$currentUserModel = Users_Record_Model::getCurrentUserModel();
		if (!$currentUserModel->isAdminUser() || !$request->has('record')) {
			throw new \Exception\NoPermittedForAdmin('LBL_PERMISSION_DENIED');
		}
	}

	public function getSize(Vtiger_Request $request)
	{
		return 'modal-lg';
	}

	public function process(Vtiger_Request $request)
	{
		$moduleName = $request->getModule();
		$qualifiedModuleName = $request->getModule(false);
		$record = $request->get('record');
		$mailDetail = OSSMail_Record_Model::getMailAccountDetail($record);
		$mailModuleActive = vtlib\Functions::getModuleId('OSSMail');
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
					$missingFolders [] = $folder['folder'];
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
