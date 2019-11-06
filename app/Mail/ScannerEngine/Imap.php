<?php
/**
 * Mail imap message file.
 *
 * @package   App
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace App\Mail\ScannerEngine;

/**
 * Mail imap message class.
 */
class Imap extends Base
{
	/**
	 * Scanner engine name.
	 *
	 * @var string
	 */
	public $name = 'Outlook';

	/**
	 * {@inheritdoc}
	 */
	public function getMailCrmId()
	{
		if ($this->has('mailCrmId')) {
			return $this->get('mailCrmId');
		}
		if (empty($this->get('message_id')) || \Config\Modules\OSSMailScanner::$ONE_MAIL_FOR_MULTIPLE_RECIPIENTS) {
			$query = (new \App\Db\Query())->select(['ossmailviewid'])->from('vtiger_ossmailview')->where(['cid' => $this->getUniqueId()])->limit(1);
		} else {
			$query = (new \App\Db\Query())->select(['ossmailviewid'])
				->from('vtiger_ossmailview')
				->where(['uid' => $this->get('message_id'), 'rc_user' => $this->getAccountOwner()])->limit(1);
		}
		$mailCrmId = $query->scalar();
		$this->set('mailCrmId', $mailCrmId);
		return  $mailCrmId;
	}
}
