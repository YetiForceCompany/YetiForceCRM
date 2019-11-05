<?php
/**
 * Mail outlook message file.
 *
 * @package   App
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace App\Mail\ScannerEngine;

/**
 * Mail outlook message class.
 */
class Outlook extends Base
{
	/**
	 * {@inheritdoc}
	 */
	public function process(): void
	{
		foreach ($this->getActions() as $action) {
			$class = "App\\Mail\\ScannerAction\\{$action}";
			(new $class($this))->process();
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function getActions(): array
	{
		return ['CreatedMail', 'LinkByFields'];
	}

	/**
	 * {@inheritdoc}
	 */
	public function getMailCrmId()
	{
		if ($this->has('mailCrmId')) {
			return $this->get('mailCrmId');
		}
		$mailCrmId = \App\Mail\Message::findByCid($this->getCid());
		$this->set('mailCrmId', $mailCrmId);
		return  $mailCrmId;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getUserId(): int
	{
		return \App\User::getCurrentUserRealId();
	}

	/**
	 * Initialize with request data.
	 *
	 * @param \App\Request $request
	 *
	 * @return void
	 */
	public function initFromRequest(\App\Request $request)
	{
		$this->set('subject', $request->isEmpty('mailSubject') ? '-' : \App\TextParser::textTruncate($request->getByType('mailSubject', 'Text'), 65535, false));
		$this->set('from_email', $request->getByType('mailFrom', 'Email'));
		$this->set('date', $request->getByType('mailDateTimeCreated', 'DateTimeInIsoFormat'));
		$this->set('message_id', $request->getByType('mailMessageId', 'MailId'));
		if ($request->has('mailTo')) {
			$this->set('to_email', $request->getArray('mailTo', 'Email'));
		}
		if ($request->has('mailCc')) {
			$this->set('cc_email', $request->getArray('mailCc', 'Email'));
		}
		if ($request->has('mailBody')) {
			$this->set('body', $request->getForHtml('mailBody'));
		}
	}
}
