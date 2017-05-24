<?php

/**
 *
 * @package YetiForce.View
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 2.0 (licenses/License.html or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class OSSMail_compose_View extends OSSMail_index_View
{

	public function preProcess(\App\Request $request, $display = true)
	{
		$this->initAutologin();
	}

	public function process(\App\Request $request)
	{
		$currentUser = Users_Record_Model::getCurrentUserModel();
		if (strpos($this->mainUrl, '?') !== false) {
			$this->mainUrl .= '&';
		} else {
			$this->mainUrl .= '?';
		}
		$this->mainUrl .= '_task=mail&_action=compose&_extwin=1';
		$params = OSSMail_Module_Model::getComposeParam($request);
		$key = md5(count($params) . microtime());

		$db = PearDatabase::getInstance();
		$db->delete('u_yf_mail_compose_data', '`userid` = ?;', [$currentUser->getId()]);
		$db->insert('u_yf_mail_compose_data', [
			'key' => $key,
			'userid' => $currentUser->getId(),
			'data' => json_encode($params),
		]);
		$this->mainUrl .= '&_composeKey=' . $key;
		header('Location: ' . $this->mainUrl);
	}

	public function postProcess(\App\Request $request)
	{
		
	}
}
