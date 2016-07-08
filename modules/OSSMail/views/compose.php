<?php

/**
 *
 * @package YetiForce.views
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class OSSMail_compose_View extends OSSMail_index_View
{

	protected $mainUrl = 'modules/OSSMail/roundcube/?_task=mail&_action=compose';

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
		if ($request->has('crmModule')) {
			$currentUser = Users_Record_Model::getCurrentUserModel();
			$moduleConfig = AppConfig::module($request->get('crmModule'));
			if ($moduleConfig && isset($moduleConfig['SEND_IDENTITY'][$currentUser->get('roleid')])) {
				$param .= '&from=' . $moduleConfig['SEND_IDENTITY'][$currentUser->get('roleid')];
			}
		}

		$this->mainUrl .= $param;

		if ($config['popup']) {
			header('Location: ' . $this->mainUrl . '&_extwin=1');
			exit;
		}
		parent::preProcess($request, $display);
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
