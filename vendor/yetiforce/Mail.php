<?php
namespace App;

/**
 * Mail basic class
 * @package YetiForce.App
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Mail
{

	/**
	 * Get a list of all smtp servers
	 * @return array
	 */
	public static function getAll()
	{
		if (Cache::has('SmtpServers', 'all')) {
			return Cache::get('SmtpServers', 'all');
		}
		$all = (new Db\Query())->from('s_#__mail_smtp')->indexBy('id')->all();
		Cache::save('SmtpServers', 'all', $all, Cache::LONG);
		return $all;
	}

	/**
	 * Get smtp server by id
	 * @param int $smtpId
	 * @return array
	 */
	public static function getSmtpById($smtpId)
	{
		if (Cache::has('SmtpServer', $smtpId)) {
			return Cache::get('SmtpServer', $smtpId);
		}
		$servers = static::getAll();
		$smtp = false;
		if (isset($servers[$smtpId])) {
			$smtp = $servers[$smtpId];
		}
		Cache::save('SmtpServer', $smtpId, $smtp, Cache::LONG);
		return $smtp;
	}
}
