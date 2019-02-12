<?php
/**
 * System warnings cron.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
$html = '';
foreach (\App\SystemWarnings::getWarnings('all') as $warning) {
	if ($warning->getFolder() === 'YetiForce') {
		continue;
	}
	$html .= '<h2>' . App\Language::translate($warning->getTitle(), 'Settings:SystemWarnings') . '</h2>';
	$html .= '<p>' . $warning->getDescription() . '</p>';
	$html .= '<hr>';
}
if (empty($html)) {
	return;
}
$html .= '<hr>' . AppConfig::main('site_URL') . '<br>';
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
