<?php

/**
 * Password authorization method class.
 *
 * @package   Auth
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

namespace App\Auth;

/**
 * Password authorization class.
 */
class Pass extends Base
{
	public const FORCE_PASSWORD_CHANGE = 1;

	/**
	 * {@inheritdoc}
	 */
	public function verify()
	{
		$password = $this->request->getRaw('password');
		$userModel = \App\User::getUserModel($this->userId);
		if (!$userModel->isActive()) {
			$hash = password_hash('', PASSWORD_BCRYPT, ['cost' => \App\Config::security('USER_ENCRYPT_PASSWORD_COST')]);
			$this->errorMessage = 'LBL_INVALID_USER_OR_PASSWORD';
		} else {
			$hash = $userModel->getDetail('user_password');
		}
		if ($result = password_verify($password, $hash)) {
			if ($this->passwordChangeRequired()) {
				$result = false;
				$this->errorMessage = 'LBL_YOUR_PASSWORD_HAS_EXPIRED';
			}
		} else {
			$this->errorMessage = 'LBL_INVALID_USER_OR_PASSWORD';
		}
		return $result;
	}

	/**
	 * Function verifies if password change is required.
	 *
	 * @return bool
	 */
	public function passwordChangeRequired(): bool
	{
		$userModel = \App\User::getUserModel($this->userId);
		$passConfig = \Settings_Password_Record_Model::getUserPassConfig();
		$time = (int) $passConfig['change_time'];
		$result = false;
		if (self::FORCE_PASSWORD_CHANGE === (int) $userModel->getDetail('force_password_change')) {
			$result = true;
		} elseif (0 === $time) {
			$result = false;
		} elseif (strtotime("-{$time} day") > strtotime($userModel->getDetail('date_password_change'))) {
			$time += (int) $passConfig['lock_time'];
			if (strtotime("-{$time} day") > strtotime($userModel->getDetail('date_password_change'))) {
				$result = true;
			}
		}
		return $result;
	}

	/**
	 * {@inheritdoc}
	 */
	public function isActive(): bool
	{
		return true;
	}
}
