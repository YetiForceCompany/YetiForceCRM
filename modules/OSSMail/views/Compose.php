<?php
/**
 * Compose view class.
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

/**
 * Compose view class.
 */
class OSSMail_Compose_View extends OSSMail_Index_View
{
	/**
	 * Pre process.
	 *
	 * @param \App\Request $request
	 * @param bool         $display
	 */
	public function preProcess(App\Request $request, $display = true)
	{
		$this->initAutologin();
	}

	/**
	 * Process.
	 *
	 * @param \App\Request $request
	 */
	public function process(App\Request $request)
	{
		$currentUser = Users_Record_Model::getCurrentUserModel();
		$this->mainUrl .= '&_task=mail&_action=compose&_extwin=1';
		$params = OSSMail_Module_Model::getComposeParam($request);
		$key = md5(\count($params) . microtime());

		$dbCommand = \App\Db::getInstance()->createCommand();
		$dbCommand->delete('u_#__mail_compose_data', ['userid' => $currentUser->getId()])->execute();
		$dbCommand->insert('u_#__mail_compose_data', ['key' => $key, 'userid' => $currentUser->getId(), 'data' => \App\Json::encode($params)])->execute();
		$this->mainUrl .= '&_composeKey=' . $key;
		header('location: ' . $this->mainUrl);
	}

	/**
	 * Post process.
	 *
	 * @param \App\Request $request
	 * @param mixed        $display
	 */
	public function postProcess(App\Request $request, $display = true)
	{
	}
}
