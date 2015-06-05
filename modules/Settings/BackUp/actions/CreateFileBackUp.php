<?php
/* +***********************************************************************************************************************************
 * The contents of this file are subject to the YetiForce Public License Version 1.1 (the "License"); you may not use this file except
 * in compliance with the License.
 * Software distributed under the License is distributed on an "AS IS" basis, WITHOUT WARRANTY OF ANY KIND, either express or implied.
 * See the License for the specific language governing rights and limitations under the License.
 * The Original Code is YetiForce.
 * The Initial Developer of the Original Code is YetiForce. Portions created by YetiForce are Copyright (C) www.yetiforce.com. 
 * All Rights Reserved.
 * *********************************************************************************************************************************** */

class Settings_BackUp_CreateFileBackUp_Action extends Settings_Vtiger_Basic_Action {
	private $filename = '';

	public function process(Vtiger_Request $request) {
		$log = vglobal('log');
		$log->info('BackUp - Start files backup');
		if (!extension_loaded('zip')) {
			$log->fatal('BackUp - No library ZIP');
			echo json_encode(array('percentage' => 100));
			exit;
		}
		$backupModel = new Settings_BackUp_Module_Model();
		$newBackup = $backupModel->clearBackupFilesTable();

		$dirsFromConfig = $backupModel->getConfig('folder');
		$dirs = array_filter(array_merge(glob('*'), glob('.htaccess')));
		$dirs = array_diff($dirs, array('cache'));
		if ('true' != $dirsFromConfig['storage_folder'])
			$dirs = array_diff($dirs, ['storage']);
		if ('true' != $dirsFromConfig['backup_folder'])
			$dirs = array_diff($dirs, [$backupModel->destDir]);

		$backUpInfo = $backupModel->getBackUpInfo();
		$sqlFileName = $backUpInfo['file_name'];

		$this->fileName = $sqlFileName . '.files';
		if ($request->get('backUpAction') == 'cron')
			$cron = TRUE;
		else
			$cron = FALSE;

		if ($newBackup) {
			$log->info('BackUp - New files backup');
			foreach ($dirs as $dir) {
				$backupModel->generateFilesStructure($dir);
			}
		}
		$backupModel->zipData($fileName, $cron);

		$zip = new ZipArchive();
		$zip->open($backupModel->destDir . '/' . $sqlFileName . '.zip', ZipArchive::CREATE);
		$zip->addFile($backupModel->tempDir . '/' . $sqlFileName . '.db.zip', "db.zip");
		$zip->addFile($backupModel->tempDir . '/' . $this->fileName . '.zip', "files.zip");
		$zip->close();

		$backupModel->sendBackupToFTP($backupModel->destDir . '/', $sqlFileName . '.zip');
		$backupModel->sendNotificationEmail();
		$backupModel->setBackUp();
		$backupModel->deleteTmpBackUpContent();
		$backupModel->deleteFile($sqlFileName . '.db.zip');
		$backupModel->deleteFile($this->fileName . '.zip');

		echo json_encode(array('percentage' => 100));
		$log->info('BackUp - End files backup');
	}

}
