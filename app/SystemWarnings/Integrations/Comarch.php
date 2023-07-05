<?php

/**
 * Check errors while sending the message file.
 *
 * @package App
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace App\SystemWarnings\Integrations;

/**
 * Check for errors while sending the message class.
 */
class Comarch extends \App\SystemWarnings\Template
{
	/** {@inheritdoc} */
	protected $title = 'LBL_CHECK_COMARCH_INTEGRATION_LOG';

	/**
	 * Checks for suspended email accounts.
	 *
	 * @return void
	 */
	public function process(): void
	{
		$data = (new \App\Db\Query())->select(['time', 'message'])->from(\App\Integrations\Comarch::LOG_TABLE_NAME)
			->where([
				'and',
				['error' => 1],
				['>=', 'time', date('Y-m-d H:i:s', strtotime('-24 hours'))]
			])
			->all(\App\DB::getInstance('log'));
		if ($data) {
			$this->status = 0;
			$this->description = \App\TextUtils::getHtmlTable($data, [
				'time' => \App\Language::translate('LBL_TIME', 'Settings:Log'),
				'message' => \App\Language::translate('LBL_MESSAGE', 'Settings:Comarch')
			]);
			if (\App\Security\AdminAccess::isPermitted('Log')) {
				$this->link = 'index.php?parent=Settings&module=Log&view=LogsViewer&type=mail';
				$this->linkTitle = \App\Language::translate('LBL_LOGS_VIEWER', 'Settings:Log');
			}
		} else {
			$this->status = 1;
		}
	}
}
