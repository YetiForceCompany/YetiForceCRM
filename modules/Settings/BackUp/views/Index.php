<?php

/**
 * @package YetiForce.views
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author Adrian Koń <a.kon@yetiforce.com>
 */
class Settings_BackUp_Index_View extends Settings_Vtiger_Index_View
{

	public function process(Vtiger_Request $request)
	{
		$viewer = $this->getViewer($request);
		$backupModel = Settings_BackUp_Module_Model::getCleanInstance();
		$ftpSettings = $backupModel->getFTPSettings();
		if ($ftpSettings != false) {
			$viewer->assign('FTP_HOST', $ftpSettings['host']);
			$viewer->assign('FTP_LOGIN', $ftpSettings['login']);
			$password = $backupModel->encrypt_decrypt('decrypt', $ftpSettings['password']);
			$viewer->assign('FTP_PASSWORD', $password);
			$viewer->assign('FTP_CONNECTION_STATUS', $ftpSettings['status']);
			$viewer->assign('FTP_PORT', $ftpSettings['port']);
			$viewer->assign('FTP_ACTIVE', $ftpSettings['active']);
			$viewer->assign('FTP_PATH', $ftpSettings['path']);
		}
		$dirsFromConfig = $backupModel->getConfig('folder');
		$mainConfig = $backupModel->getConfig('main');
		$usersForNotifications = $backupModel->getUsersForNotifications();
		$adminUsers = Users_Module_Model::getAdminUsers();
		$backUpInfo = $backupModel->getBackUpInfo();
		$moduleName = $request->getModule();
		$qualifiedModuleName = $request->getModule(false);
		$pagination = Settings_BackUp_Pagination_Action::process($request);
		$pagination = json_decode($pagination, true);
		$viewer->assign('BACKUP_MODEL', $backupModel);
		$viewer->assign('BACKUP_INFO', $backupModel->getBackupInfo());
		$viewer->assign('DIRSFROMCONFIG', $dirsFromConfig);
		$viewer->assign('MAIN_CONFIG', $mainConfig);
		$viewer->assign('USERFORNOTIFICATIONS', $usersForNotifications);
		$viewer->assign('PREV_PAGE', $pagination['prevPage']);
		$viewer->assign('NEXT_PAGE', $pagination['nextPage']);
		$viewer->assign('OFFSET', $pagination['offset']);
		$viewer->assign('ALL_PAGES', $pagination['allPages']);
		$viewer->assign('PAGE', $pagination['page']);
		$viewer->assign('MODULE', $moduleName);
		$viewer->assign('BACKUP_EXIST', $backUpInfo['status']);
		$viewer->assign('BACKUPS', $pagination['backups']);
		$viewer->assign('QUALIFIED_MODULE_NAME', $qualifiedModuleName);
		$viewer->assign('ADMIN_USERS', $adminUsers);
		$viewer->view('Index.tpl', $qualifiedModuleName);
	}
}
