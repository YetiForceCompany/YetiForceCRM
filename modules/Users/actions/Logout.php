<?php
/**
 * Logout action file.
 *
 * @package   Action
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

namespace Modules\Users\Actions;

/**
 * Logout action class.
 */
class Logout extends \App\Controller\Action
{
	/**
	 * {@inheritdoc}
	 */
	public function checkPermission()
	{
		return true;
	}

	/**
	 * {@inheritdoc}
	 */
	public function process()
	{
		$eventHandler = new \App\EventHandler();
		$eventHandler->trigger('UserLogoutBefore');
		if (\App\Config::main('session_regenerate_id')) {
			\App\Session::regenerateId(true); // to overcome session id reuse.
		}
		\OSSMail_Logout_Model::logoutCurrentUser();
		\App\Session::destroy();

		$moduleName = $this->request->getModule();
		$moduleModel = \Users_Module_Model::getInstance($moduleName);
		$moduleModel->saveLogoutHistory();
		$this->response->setResult(!\App\Session::has('authenticated_user_id'));
	}

	/**
	 * {@inheritdoc}
	 */
	public function validateRequest()
	{
		$this->request->validateReadAccess();
	}
}
