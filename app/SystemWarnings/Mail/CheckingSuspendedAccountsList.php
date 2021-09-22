<?php

/**
 * Checking suspended accounts list file.
 *
 * @package App
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 4.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Arkadiusz Sołek <a.solek@yetiforce.com>
 */

namespace App\SystemWarnings\Mail;

/**
 * Checking suspended accounts list class.
 */
class CheckingSuspendedAccountsList extends \App\SystemWarnings\Template
{
	/**
	 * @var string Modal header title
	 */
	protected $title = 'LBL_CHECK_SUSPENDED_ACCOUNTS';

	/**
	 * Checks for suspended email accounts.
	 *
	 * @return void
	 */
	public function process(): void
	{
		$data = (new \App\Db\Query())->from('roundcube_users')->where(['crm_status' => 2]);
		if (0 === $data->count()) {
			$this->status = 1;
		} else {
			$this->status = 0;
			$userSuspendedList = [];
			foreach ($data->all() as $value) {
				$userSuspendedList[] = $value['username'];
			}
			$this->description = \App\Language::translateArgs('LBL_CHECK_SUSPENDED_ACCOUNTS_DESC', 'Settings:SystemWarnings', implode(', ', $userSuspendedList));
		}
	}
}
