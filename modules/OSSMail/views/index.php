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

class OSSMail_index_View extends Vtiger_Index_View
{

	protected $mainUrl = 'modules/OSSMail/roundcube/';

	public function __construct()
	{
		parent::__construct();
		$this->mainUrl = OSSMail_Record_Model::getSiteUrl() . $this->mainUrl;
	}

	public function initAutologin()
	{
		$config = Settings_Mail_Config_Model::getConfig('autologin');
		if ($config['autologinActive'] == 'true') {
			$account = OSSMail_Autologin_Model::getAutologinUsers();
			if ($account) {
				$rcUser = (isset($_SESSION['AutoLoginUser']) && array_key_exists($_SESSION['AutoLoginUser'], $account)) ? $account[$_SESSION['AutoLoginUser']] : reset($account);

				$key = md5($rcUser['rcuser_id'] . microtime());
				if (strpos($this->mainUrl, '?') !== false) {
					$this->mainUrl .= '&';
				} else {
					$this->mainUrl .= '?';
				}
				$this->mainUrl .= '_autologin=1&_autologinKey=' . $key;
				$db = PearDatabase::getInstance();
				$currentUserModel = Users_Record_Model::getCurrentUserModel();
				$userId = $currentUserModel->getId();
				$params = ['language' => Vtiger_Language_Handler::getLanguage()];
				$db->delete('u_yf_mail_autologin', '`cuid` = ?;', [$userId]);
				$db->insert('u_yf_mail_autologin', [
					'key' => $key,
					'ruid' => $rcUser['rcuser_id'],
					'cuid' => $userId,
					'params' => \App\Json::encode($params)
				]);
			}
		}
	}

	public function preProcess(Vtiger_Request $request, $display = true)
	{
		$this->initAutologin();

		parent::preProcess($request, $display);
	}

	public function process(Vtiger_Request $request)
	{
		$moduleName = $request->getModule();
		$viewer = $this->getViewer($request);
		$viewer->assign('URL', $this->mainUrl);
		$viewer->view('index.tpl', $moduleName);
	}
}
