<?php
/**
 * Check server ip.
 *
 * @package App
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Arkadiusz Sołek <a.solek@yetiforce.com>
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace App\SystemWarnings\Security;

/**
 * Check server ip in black list.
 */
class CheckServerIpInBlackList extends \App\SystemWarnings\Template
{
	/** {@inheritdoc} */
	protected $title = 'LBL_CHECK_SERVER_BLACK_LIST_IP';

	/** {@inheritdoc} */
	protected $priority = 9;

	/**
	 * checks if a given ip address is on the blacklist.
	 *
	 * @return void
	 */
	public function process(): void
	{
		$this->status = 1;
		if (($ip = \App\RequestUtil::getRemoteIP(true)) && ($blackList = \App\Mail\Rbl::findIp($ip, true))) {
			foreach ($blackList as $row) {
				if ((\App\Mail\Rbl::LIST_TYPE_BLACK_LIST === (int) $row['type']) || (\App\Mail\Rbl::LIST_TYPE_PUBLIC_BLACK_LIST === (int) $row['type'])) {
					$this->status = 0;
					break;
				}
			}
			if (!$this->status) {
				$this->description = \App\Language::translate('LBL_BLACK_LIST_ALERT', 'OSSMail');
			}
		}
	}
}
