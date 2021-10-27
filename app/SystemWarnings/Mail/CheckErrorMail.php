<?php

/**
 * Check errors while sending the message file.
 *
 * @package App
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 4.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Arkadiusz Sołek <a.solek@yetiforce.com>
 */

namespace App\SystemWarnings\Mail;

/**
 * Check for errors while sending the message class.
 */
class CheckErrorMail extends \App\SystemWarnings\Template
{
	protected $statusValue = 0;
	/** @var string Modal header title */
	protected $title = 'LBL_CHECK_ERROR_MAIL';

	/**
	 * Checks for suspended email accounts.
	 *
	 * @return void
	 */
	public function process(): void
	{
		$data = (new \App\Db\Query())->from('l_#__mail')->where(['>=', 'date', (new \DateTime(date('Y-m-d H:i:s')))->modify('-24 hours')->format('Y-m-d H:i:s')])->all();
		if (!$data) {
			$this->status = 1;
		} else {
			$this->status = 0;
			$this->description = \App\Language::translateArgs('LBL_CHECK_ERROR_MAIL_DESC', 'Settings:SystemWarnings', \count($data));

		}
	}
}
