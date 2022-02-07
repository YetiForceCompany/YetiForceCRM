<?php

/**
 * Check errors while sending the message file.
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
 * Check for errors while sending the message class.
 */
class CheckErrorMail extends \App\SystemWarnings\Template
{
	/** {@inheritdoc} */
	protected $statusValue = 0;

	/** {@inheritdoc} */
	protected $title = 'LBL_CHECK_MAIL_QUEUE_ERROR';

	/**
	 * Checks for suspended email accounts.
	 *
	 * @return void
	 */
	public function process(): void
	{
		$count = (new \App\Db\Query())->from('l_#__mail')->where(['>=', 'date', (new \DateTime())->modify('-24 hours')->format('Y-m-d H:i:s')])->count();
		if ($count) {
			$this->status = 0;
			$this->description = \App\Language::translateArgs('LBL_CHECK_ERROR_MAIL_DESC', 'Settings:SystemWarnings', $count);
			if (\App\Security\AdminAccess::isPermitted('Log')) {
				$this->link = 'index.php?parent=Settings&module=Log&view=LogsViewer&type=mail';
				$this->linkTitle = \App\Language::translate('LBL_LOGS_VIEWER', 'Settings:Log');
			}
		} else {
			$this->status = 1;
		}
	}
}
