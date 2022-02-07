<?php

/**
 * Checking reports for verification file.
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
 * Checking reports for verification class.
 */
class CheckingReportsForVerification extends \App\SystemWarnings\Template
{
	/** {@inheritdoc} */
	protected $title = 'LBL_CHECK_REPORTS_FOR_FORVERIFICATION';

	/**
	 * Checking reports for verification.
	 *
	 * @return void
	 */
	public function process(): void
	{
		$count = (new \App\Db\Query())->from('s_#__mail_rbl_request')->where(['type' => [\App\Mail\Rbl::LIST_TYPE_BLACK_LIST, \App\Mail\Rbl::LIST_TYPE_WHITE_LIST], 'status' => 0])->count();
		if (0 === $count) {
			$this->status = 1;
		} else {
			$this->status = 0;
			$this->description = \App\Language::translateArgs('LBL_CHECK_REPORTS_FOR_FORVERIFICATION_DESC', 'Settings:SystemWarnings', $count);
			if (\App\Security\AdminAccess::isPermitted('MailRbl')) {
				$this->link = 'index.php?parent=Settings&module=MailRbl&view=Index';
				$this->linkTitle = \App\Language::translate('MailRbl', 'Settings:MailRbl');
			}
		}
	}
}
