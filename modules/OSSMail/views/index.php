<?php

/**
 * OSSMail index view class
 * @package YetiForce.View
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 2.0 (licenses/License.html or yetiforce.com)
 */
class OSSMail_index_View extends Vtiger_Index_View
{

	protected $mainUrl = 'modules/OSSMail/roundcube/';

	public function __construct()
	{
		parent::__construct();
		if (!IS_PUBLIC_DIR) {
			$this->mainUrl = 'public_html/' . $this->mainUrl;
		}
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
				$params = ['language' => \App\Language::getLanguage()];
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

	public function preProcess(\App\Request $request, $display = true)
	{
		$this->initAutologin();

		parent::preProcess($request, $display);
	}

	public function process(\App\Request $request)
	{
		$moduleName = $request->getModule();
		$viewer = $this->getViewer($request);
		$viewer->assign('URL', $this->mainUrl);
		$viewer->view('index.tpl', $moduleName);
	}
}
