<?php
/**
 * Check server ip.
 *
 * @package   App
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Arkadiusz Sołek <a.solek@yetiforce.com>
 */

namespace App\SystemWarnings\Security;

/**
 * Check server ip in black list.
 */
class CheckServerIpInBlackList extends \App\SystemWarnings\Template
{
	/**
	 * @var string Modal header title
	 */
	protected $title = 'LBL_CHECK_SERVER_BLACK_LIST_IP';
	/**
	 * @var int Warning priority code
	 */
	protected $priority = 9;

	/**
	 * checks if a given ip address is on the blacklist.
	 */
	public function process()
	{
		$this->status = 1;
		if (($ip = \App\RequestUtil::getRemoteIP(true)) && ($blackList = \App\Mail\Rbl::findIp($ip))) {
			foreach ($blackList as $row) {
				if (1 !== (int) $row['status'] && (\App\Mail\Rbl::LIST_TYPE_BLACK_LIST === (int) $row['type']) || (\App\Mail\Rbl::LIST_TYPE_PUBLIC_BLACK_LIST === (int) $row['type'])) {
					$this->status = 0;
				}
				break;
			}
			if (!$this->status) {
				$this->description = \App\Language::translate('LBL_BLACK_LIST_ALERT', 'OSSMail');
			}
		}
	}
}
