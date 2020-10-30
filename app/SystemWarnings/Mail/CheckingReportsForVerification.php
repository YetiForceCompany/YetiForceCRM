<?php

/**
 * Checking reports for verification file.
 *
 * @package   App
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Arkadiusz Sołek <a.solek@yetiforce.com>
 */

namespace App\SystemWarnings\Mail;

/**
 * Checking reports for verification class.
 */
class CheckingReportsForVerification extends \App\SystemWarnings\Template
{
	/**
	 * @var string Modal header title
	 */
	protected $title = 'LBL_CHECK_REPORTS_FOR_FORVERIFICATION';

	/**
	 * Checking reports for verification.
	 */
	public function process()
	{
		$query = (new \App\Db\Query())->from('s_yf_mail_rbl_request')->where(['or', ['=', 'type', 1], ['=', 'type', 0]])->andWhere(['status' => 0]);
		if (!$query->exists()) {
			$this->status = 1;
		} else {
			$this->status = 0;
			$this->link = 'index.php?parent=Settings&module=MailRbl&view=Index';
			$this->description = \App\Language::translateArgs('LBL_CHECK_REPORTS_FOR_FORVERIFICATION_DESC', 'Settings:SystemWarnings', $query->count());
		}
	}
}
