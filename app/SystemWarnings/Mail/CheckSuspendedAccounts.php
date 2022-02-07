<?php

/**
 * Checking suspended accounts file.
 *
 * @package App
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Arkadiusz Sołek <a.solek@yetiforce.com>
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace App\SystemWarnings\Mail;

/**
 * Checking suspended accounts class.
 */
class CheckSuspendedAccounts extends \App\SystemWarnings\Template
{
	/** {@inheritdoc} */
	protected $title = 'LBL_CHECK_SUSPENDED_ACCOUNTS';

	/**
	 * Checks for suspended email accounts.
	 *
	 * @return void
	 */
	public function process(): void
	{
		$data = (new \App\Db\Query())->select(['username'])->from('roundcube_users')->where(['crm_status' => 2])->column();
		if (!$data) {
			$this->status = 1;
		} else {
			$this->status = 0;
			$userSuspendedList = '<ul>';
			foreach ($data as $value) {
				$userSuspendedList .= "<li> $value </li>";
			}
			$userSuspendedList .= '<ul>';
			$this->link = 'index.php?module=OSSMailScanner&parent=Settings&view=Index';
			$this->linkTitle = \App\Language::translate('OSSMailScanner', 'Settings:OSSMailScanner');
			$this->description = \App\Language::translateArgs('LBL_CHECK_SUSPENDED_ACCOUNTS_DESC', 'Settings:SystemWarnings', $userSuspendedList);
		}
	}
}
