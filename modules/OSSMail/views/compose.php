<?php
/* {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} */

class OSSMail_compose_View extends Vtiger_Index_View
{

	protected $mainUrl = '';

	function __construct()
	{
		parent::__construct();
		$this->mainUrl = OSSMail_Record_Model::GetSite_URL() . 'modules/OSSMail/roundcube/?_task=mail&_action=compose';
	}

	public function preProcess(Vtiger_Request $request, $display = true)
	{
		$config = Settings_Mail_Config_Model::getConfig('autologin');
		if ($config['autologinActive'] == 'true') {
			$account = OSSMail_Autologin_Model::getAutologinUsers();
			if ($account) {
				$rcUser = (isset($_SESSION['AutoLoginUser']) && array_key_exists($_SESSION['AutoLoginUser'], $account)) ? $account[$_SESSION['AutoLoginUser']] : reset($account);
				require_once 'modules/OSSMail/RoundcubeLogin.class.php';
				$rcl = new RoundcubeLogin($this->mainUrl, false);
				try {
					if ($rcl->isLoggedIn()) {
						if ($rcl->getUsername() != $rcUser['username']) {
							$rcl->logout();
							$rcl->login($rcUser['username'], $rcUser['password']);
						}
					} else {
						$rcl->login($rcUser['username'], $rcUser['password']);
					}
				} catch (RoundcubeLoginException $ex) {
					$log = vglobal('log');
					$log->error('OSSMail_index_View|RoundcubeLoginException: ' . $ex->getMessage());
				}
			}
		}
		$config = OSSMail_Module_Model::getComposeParameters();
		$param = OSSMail_Module_Model::getComposeUrlParam($request->get('crmModule'), $request->get('crmRecord'), $request->get('type'), $request->get('crmView'));
		if ($request->get('mid') != '' && $request->get('type') != '') {
			$param .= '&crmid=' . (int) $request->get('mid') . '&type=' . $request->get('type');
		}
		$this->mainUrl = $this->mainUrl . $param;

		if ($config['popup']) {
			header('Location: ' . $this->mainUrl . '&_extwin=1');
			exit;
		}
		parent::preProcess($request, true);
	}

	public function process(Vtiger_Request $request)
	{
		$url = '';
		if ($request->get('to') != '') {
			$to = $request->get('to');
		}
		if ($request->get('subject') != '') {
			$subject = $request->get('subject');
		}
		if (!empty($_SESSION['POST']['to'])) {
			$to = implode(",", $_SESSION['POST']['to']);
		}
		if (!empty($_SESSION['POST']['cc'])) {
			$cc = implode(",", $_SESSION['POST']['cc']);
		}
		if (!empty($_SESSION['POST']['bcc'])) {
			$bcc = implode(",", $_SESSION['POST']['bcc']);
		}
		if (!empty($_SESSION['POST']['subject'])) {
			$subject = implode(",", $_SESSION['POST']['subject']);
		}
		$mod = $_SESSION['POST']['sourceModule'];

		if ($mod == 'Campaigns') {
			if ($to != '') {
				$url .= '&bcc=' . $to;
			}
			if ($_SESSION['POST']['sourceRecord'] != '') {
				$Record_Model = Vtiger_Record_Model::getInstanceById($_SESSION['POST']['sourceRecord'], $mod);
				$campaign_no = $Record_Model->get('campaign_no');
				$url .= '&subject=' . $campaign_no . ': ' . $Record_Model->get('campaignname');
			}
		} else {
			if ($to != '') {
				$url .= '&to=' . $to;
			}
			if ($cc != '') {
				$url .= '&cc=' . $cc;
			}
			if ($bcc != '') {
				$url .= '&bcc=' . $bcc;
			}
		}
		if ($subject != '') {
			$url .= '&subject=' . $subject;
		}

		$pdfPath = $request->get('pdf_path');
		if ($pdfPath) {
			$url .= '&pdf_path=' . $pdfPath;
		}

		$viewer = $this->getViewer($request);
		$viewer->assign('URL', $this->mainUrl . $url);
		$viewer->view('index.tpl', 'OSSMail');
	}
}
