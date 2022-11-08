<?php
/**
 * Bind an existing e-mail file.
 *
 * @package   App
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

/**
 * Bind an existing e-mail class.
 */
class MailAccount_Bind_Cron extends \App\CronHandler
{
	/** {@inheritdoc} */
	public function process()
	{
		$dbCommand = App\Db::getInstance()->createCommand();
		$queryGenerator = (new \App\QueryGenerator('OSSMailView'))->setFields(['id', 'rc_user'])->addCondition('verify', 1, 'e');
		$dataReader = $queryGenerator->createQuery()->createCommand()->query();
		while ($row = $dataReader->read()) {
			$accountId = $row['rc_user'];
			$crmMailId = $row['id'];
			if (\App\Record::isExists($accountId, 'MailAccount') && ($mailAccount = \App\Mail\Account::getInstanceById($accountId))->isActive() && \in_array('BindMail', $mailAccount->getActions())) {
				$message = \App\Mail\Message\Imap::getInstanceById($crmMailId);
				(new \App\Mail\Scanner())->getAction('BindMail')->setAccount($mailAccount)->setMessage($message)->process();
			}
			$dbCommand->update('vtiger_ossmailview', ['verify' => 0], ['ossmailviewid' => $crmMailId])->execute();
		}
	}
}
