<?php
/*+***********************************************************************************************************************************
 * The contents of this file are subject to the YetiForce Public License Version 1.1 (the "License"); you may not use this file except
 * in compliance with the License.
 * Software distributed under the License is distributed on an "AS IS" basis, WITHOUT WARRANTY OF ANY KIND, either express or implied.
 * See the License for the specific language governing rights and limitations under the License.
 * The Original Code is YetiForce.
 * The Initial Developer of the Original Code is YetiForce. Portions created by YetiForce are Copyright (C) www.yetiforce.com. 
 * All Rights Reserved.
 *************************************************************************************************************************************/

class Settings_BackUp_Index_View extends Settings_Vtiger_Index_View {

	public function process(Vtiger_Request $request) {

		$viewer = $this->getViewer($request);
		$ftpSettings = Settings_BackUp_Module_Model::getFTPSettings();
		if ($ftpSettings != false) {
			$viewer->assign('FTP_HOST', $ftpSettings[1]);
			$viewer->assign('FTP_LOGIN', $ftpSettings[2]);
			$password = Settings_BackUp_Module_Model::encrypt_decrypt('decrypt', $ftpSettings[3]);
			$viewer->assign('FTP_PASSWORD', $password);
			$viewer->assign('FTP_CONNECTION_STATUS', $ftpSettings[4]);
			$viewer->assign('FTP_PORT', $ftpSettings[5]);
			$viewer->assign('FTP_ACTIVE', $ftpSettings[6]);
			$viewer->assign('FTP_PATH', $ftpSettings[7]);
		}
		$adminUsers = Users_Module_Model::getAdminUsers();
		$backUpInfo = Settings_BackUp_Module_Model::getBackUpInfo();
		$moduleName = $request->getModule();
		$qualifiedModuleName = $request->getModule(false);
		$pagination = Settings_BackUp_Pagination_Action::process($request);
		$pagination = json_decode($pagination, true);
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
