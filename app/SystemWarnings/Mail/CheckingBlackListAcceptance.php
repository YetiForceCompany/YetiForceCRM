<?php

/**
 * Checking blacklist acceptance file.
 *
 * @package   App
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Arkadiusz Sołek <a.solek@yetiforce.com>
 */

namespace App\SystemWarnings\Mail;

/**
 * Checking blacklist acceptance class.
 */
class CheckingBlackListAcceptance extends \App\SystemWarnings\Template
{
	/**
	 * @var string Modal header title
	 */
	protected $title = 'LBL_CHECK_BLACKLIST_REPORTS';

	/**
	 * Checks if there are entries on the blacklist with the status approved.
	 */
	public function process()
	{
		$query = (new \App\Db\Query())->from('s_yf_mail_rbl_list')->where(['type' => 0, 'status' => 0]);
		if (!$query->exists()) {
			$this->status = 1;
		} else {
			$this->status = 0;
			$this->link = 'index.php?parent=Settings&module=MailRbl&view=Index';
			$this->description = \App\Language::translateArgs('LBL_CHECK_BLACKLIST_REPORTS_DESC', 'Settings:SystemWarnings', $query->count());
		}
	}
}
