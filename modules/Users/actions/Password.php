<?php

/**
 * Reset password action class
 * @package YetiForce.Action
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 2.0 (licenses/License.html or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Users_Password_Action extends Vtiger_Action_Controller
{

	/**
	 * {@inheritDoc}
	 */
	public function __construct()
	{
		parent::__construct();
		$this->exposeMethod('reset');
		$this->exposeMethod('change');
		$this->exposeMethod('massReset');
	}

	/**
	 * Function to check permission
	 * @param \App\Request $request
	 * @throws \App\Exceptions\NoPermittedToRecord
	 */
	public function checkPermission(\App\Request $request)
	{
		$currentUserModel = Users_Record_Model::getCurrentUserModel();
		switch ($request->getMode()) {
			case 'reset':
				if ($currentUserModel->isAdminUser() === true || (AppConfig::security('SHOW_MY_PREFERENCES') && (int) $currentUserModel->get('id') === $request->getInteger('record'))) {
					return true;
				}
				break;
			case 'change':
				if ((int) $currentUserModel->get('id') === $request->getInteger('record')) {
					return true;
				}
				break;
			case 'massReset':
				if ($currentUserModel->isAdminUser() === true) {
					return true;
				}
				break;
		}
		throw new \App\Exceptions\NoPermittedToRecord('LBL_PERMISSION_DENIED', 406);
	}

	/**
	 * {@inheritDoc}
	 */
	public function process(\App\Request $request)
	{
		$mode = $request->getMode();
		if (!empty($mode)) {
			$this->invokeExposedMethod($mode, $request);
			return;
		}
	}

	/**
	 * Reset user password
	 * @param \App\Request $request
	 */
	public function reset(\App\Request $request)
	{
		$moduleName = $request->getModule();
		$password = \App\Encryption::getRandomPassword();
		$userRecordModel = Users_Record_Model::getInstanceById($request->getInteger('record'), $moduleName);
		$userRecordModel->set('user_password', $password);
		$userRecordModel->save();
		\App\Mailer::sendFromTemplate([
			'template' => 'UsersResetPassword',
			'moduleName' => $moduleName,
			'recordId' => $userRecordModel->getId(),
			'to' => $userRecordModel->get('email1'),
			'password' => $password,
		]);
		$response = new Vtiger_Response();
		$response->setResult(['notify' => ['text' => \App\Language::translate('LBL_PASSWORD_WAS_RESET_AND_SENT_TO_USER', 'Users')]]);
		$response->emit();
	}

	/**
	 * Change user password
	 * @param \App\Request $request
	 */
	public function change(\App\Request $request)
	{
		$moduleName = $request->getModule();
		$password = $request->getRaw('password');
		$userRecordModel = Users_Record_Model::getInstanceById(App\User::getCurrentUserId(), $moduleName);
		$response = new Vtiger_Response();
		if ($password !== $request->getRaw('confirmPassword')) {
			$response->setResult(['procesStop' => true, 'notify' => ['text' => \App\Language::translate('LBL_PASSWORD_SHOULD_BE_SAME', 'Users'), 'type' => 'error']]);
		} elseif (!$userRecordModel->verifyPassword($request->getRaw('oldPassword'))) {
			$response->setResult(['procesStop' => true, 'notify' => ['text' => \App\Language::translate('LBL_INCORRECT_OLD_PASSWORD', 'Users'), 'type' => 'error']]);
		} else {
			$userRecordModel->set('user_password', $request->getRaw('password'));
			try {
				$userRecordModel->save();
				$response->setResult(['notify' => ['text' => \App\Language::translate('LBL_PASSWORD_WAS_RESET_AND_SENT_TO_USER', 'Users')]]);
			} catch (\App\Exceptions\SaveRecord $exc) {
				$response->setResult(['procesStop' => true, 'notify' => ['text' => \App\Language::translateSingleMod($exc->getMessage(), 'Other.Exceptions'), 'type' => 'error']]);
			} catch (\App\Exceptions\Security $exc) {
				$response->setResult(['procesStop' => true, 'notify' => ['text' => $exc->getMessage(), 'type' => 'error']]);
			}
		}
		$response->emit();
	}

	/**
	 * Mass reset user password
	 * @param \App\Request $request
	 */
	public function massReset(\App\Request $request)
	{
		$moduleName = $request->getModule();
		$recordsList = Vtiger_Mass_Action::getRecordsListFromRequest($request);
		foreach ($recordsList as $userId) {
			$password = \App\Encryption::getRandomPassword();
			$userRecordModel = Users_Record_Model::getInstanceById($userId, $moduleName);
			$userRecordModel->set('user_password', $password);
			$userRecordModel->save();
			\App\Mailer::sendFromTemplate([
				'template' => 'UsersResetPassword',
				'moduleName' => $moduleName,
				'recordId' => $userRecordModel->getId(),
				'to' => $userRecordModel->get('email1'),
				'password' => $password,
			]);
		}
		$response = new Vtiger_Response();
		$response->setResult(['notify' => ['text' => \App\Language::translate('LBL_PASSWORD_WAS_RESET_AND_SENT_TO_USERS', 'Users')]]);
		$response->emit();
	}
}
