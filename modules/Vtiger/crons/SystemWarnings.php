<?php
/**
 * System warnings cron.
 *
 * @package   Cron
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

/**
 * Vtiger_SystemWarnings_Cron class.
 */
class Vtiger_SystemWarnings_Cron extends \App\CronHandler
{
	/** {@inheritdoc} */
	public function process()
	{
		$html = '';
		foreach (\App\SystemWarnings::getWarnings('all') as $warning) {
			if ('YetiForce' === $warning->getFolder()) {
				continue;
			}
			$html .= '<h2>' . App\Language::translate($warning->getTitle(), 'Settings:SystemWarnings') . '</h2>';
			$html .= '<p>' . $warning->getDescription() . '</p>';
			$html .= '<hr>';
		}
		if (empty($html)) {
			return;
		}
		$html .= '<hr>' . App\Config::main('site_URL') . '<br>';
		$company = \current(\App\MultiCompany::getAll() ?? [[]]);
		if (!empty($company['company_name'])) {
			$html .= ' - ' . $company['company_name'];
		}
		if (!empty($company['email1'])) {
			$html .= ' - ' . $company['email1'];
		}
		$mails = (new \App\Db\Query())->select(['email1'])->from('vtiger_users')->where(['is_admin' => 'on', 'status' => 'Active'])->column();
		if ($mails) {
			\App\Mailer::sendFromTemplate([
				'to' => $mails,
				'template' => 'SystemWarnings',
				'warnings' => $html,
			]);
		}
	}
}
