<?php

/**
 *
 * @package YetiForce.views
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class OSSMail_compose_View extends Vtiger_Index_View
{

	protected $mainUrl = '';

	function __construct()
	{
		parent::__construct();
		$this->mainUrl = OSSMail_Record_Model::GetSite_URL() . 'modules/OSSMail/roundcube/?_task=mail&_action=compose';
	}

	function initAutologin()
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
	}

	function preProcessAjax(Vtiger_Request $request)
	{
		$this->initAutologin();
		$this->mainUrl = $this->mainUrl . '&_extwin=1';
	}

	public function preProcess(Vtiger_Request $request, $display = true)
	{
		$this->initAutologin();
		$config = OSSMail_Module_Model::getComposeParameters();
		$param = OSSMail_Module_Model::getComposeUrlParam($request->get('crmModule'), $request->get('crmRecord'), $request->get('type'), $request->get('crmView'));
		if ($request->get('mid') != '' && $request->get('type') != '') {
			$param .= '&crmid=' . (int) $request->get('mid') . '&type=' . $request->get('type');
		}
		$pdfPath = $request->get('pdf_path');
		if ($pdfPath) {
			$param .= '&pdf_path=' . $pdfPath;
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
		$post = [];
		$crmModule = $request->get('crmModule');
		$crmRecord = $request->get('crmRecord');

		if ($request->get('to') != '') {
			$to = $request->get('to');
		}
		if ($request->get('subject') != '') {
			$subject = $request->get('subject');
		}
		if ($crmModule == 'Campaigns' && !empty($crmRecord)) {
			$recordModel = Vtiger_Record_Model::getInstanceById($crmRecord, $crmModule);
			$subject = $recordModel->get('campaign_no') . ' - ' . $recordModel->get('campaignname');
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

		if ($request->has('emails')) {
			$post['emails'] = Vtiger_Util_Helper::toSafeHTML(Zend_Json::encode($request->get('emails')));
		}
		$db = PearDatabase::getInstance();
		$result = $db->pquery('SELECT vars FROM roundcube_session WHERE sess_id=?', [$_COOKIE['roundcube_sessid']]);
		$vars = $db->getSingleValue($result);
		if (!empty($vars)) {
			$vars = base64_decode($vars);
			$vars = $this->unSerializeSession($vars);
			$post['_token'] = $vars['request_token'];
		}

		$viewer = $this->getViewer($request);
		$viewer->assign('POST_DATA', $post);
		$viewer->assign('URL', $this->mainUrl . $url);

		if ($request->isAjax()) {
			$viewer->view('ComposePopup.tpl', 'OSSMail');
		} else {
			$viewer->view('index.tpl', 'OSSMail');
		}
	}

	function unSerializeSession($data)
	{
		$vars = preg_split(
			'/([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)\|/', $data, -1, PREG_SPLIT_NO_EMPTY |
			PREG_SPLIT_DELIM_CAPTURE
		);
		for ($i = 0; $vars[$i]; $i++) {
			$result[$vars[$i++]] = unserialize($vars[$i]);
		}
		return $result;
	}
}
