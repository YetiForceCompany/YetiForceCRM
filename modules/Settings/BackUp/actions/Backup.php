<?php

/**
 * @package YetiForce.actions
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Settings_BackUp_Backup_Action extends Settings_Vtiger_Basic_Action
{

	public function __construct()
	{
		parent::__construct();
		$this->exposeMethod('perform');
		$this->exposeMethod('progress');
		$this->exposeMethod('saveftp');
		$this->exposeMethod('stopBackup');
	}

	public function perform(Vtiger_Request $request)
	{
		$backupModel = Settings_BackUp_Module_Model::getCleanInstance();
		$backupModel->scheduleBackup();

		$response = new Vtiger_Response();
		$response->setResult(array(
			'success' => true,
			'message' => vtranslate('LBL_SET_SCHEDULE_BACKUP', $request->getModule(false))
		));
		$response->emit();
	}

	public function progress(Vtiger_Request $request)
	{
		$id = $request->get('id');
		$backupModel = Settings_BackUp_Module_Model::getCleanInstance();
		$progress = $backupModel->getProgress($id);

		$response = new Vtiger_Response();
		$response->setResult($progress);
		$response->emit();
	}

	public function saveftp(Vtiger_Request $request)
	{
		$adb = PearDatabase::getInstance();
		$log = vglobal('log');
		$log->debug('Settings_BackUp_SaveFTPConfig_Action: process started');
		$ftpServerName = $request->get('ftpservername');
		$ftpLogin = $request->get('ftplogin');
		$ftpPassword = $request->get('ftppassword');
		$ftpPort = $request->get('ftpport');
		$ftpPath = $request->get('ftppath');
		$ftpActive = $request->get('ftpactive');
		if ('true' == $ftpActive)
			$ftpActive = TRUE;
		else
			$ftpActive = FALSE;

		if ('' != $ftpPort) {
			$ftpConnect = @ftp_connect($ftpServerName, $ftpPort);
		} else {
			$ftpConnect = @ftp_connect($ftpServerName);
			$ftpPort = NULL;
		}

		if (!$ftpConnect) {
			$result = array('success' => true, 'fptConnection' => false, 'message' => 'JS_HOST_NOT_CORRECT');
		} else {
			$loginResult = @ftp_login($ftpConnect, $ftpLogin, $ftpPassword);
			if (FALSE == $loginResult) {
				$log->debug('FTP connection has failed!');
				$result = array('success' => true, 'fptConnection' => false, 'message' => 'JS_CONNECTION_FAIL');
			} else {
				$log->debug('FTP connection has success!');
				$result = array('success' => true, 'fptConnection' => true, 'message' => 'JS_SAVE_CHANGES');
				Settings_BackUp_Module_Model::saveFTPSettings($ftpServerName, $ftpLogin, $ftpPassword, TRUE, $ftpPort, $ftpActive, $ftpPath);
			}
		}
		$response = new Vtiger_Response();
		$response->setResult($result);
		$response->emit();
	}

	public static function stopBackup()
	{
		$log = vglobal('log');
		Settings_BackUp_Module_Model::stopBackup();
		$response = new Vtiger_Response();
		$response->setResult([]);
		$response->emit();
	}
}
