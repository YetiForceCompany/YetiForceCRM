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
			if (!empty($smtp['from'])) {
				$smtp['from'] = Json::decode($smtp['from']);
			}
			if (!empty($smtp['replay_to'])) {
				$smtp['replay_to'] = Json::decode($smtp['replay_to']);
			}
		}
		Cache::save('SmtpServer', $smtpId, $smtp, Cache::LONG);
		return $smtp;
	}

	/**
	 * Get mail template
	 * @param int|string $id
	 * @return array
	 */
	public static function getTemplete($id, $parse = true)
	{
		$detail = static::getTempleteDetail($id);
		return array_merge(
			$detail, static::getTempleteAttachments($detail['ossmailtemplatesid'])
		);
	}

	/**
	 * Get mail template detail
	 * @param int|string $id
	 * @return array
	 */
	public static function getTempleteDetail($id)
	{
		if (Cache::has('MailTempleteDetail', $id)) {
			return Cache::get('MailTempleteDetail', $id);
		}
		$query = (new \App\Db\Query())->from('vtiger_ossmailtemplates');
		if (is_numeric($id)) {
			$query->where(['ossmailtemplatesid' => $id]);
		} else {
			$query->where(['sysname' => $id]);
		}
		$row = $query->one();
		Cache::save('MailTempleteDetail', $id, $row, Cache::LONG);
		return $row;
	}

	/**
	 * Get mail template attachments
	 * @param int|string $id
	 * @return array
	 */
	public static function getTempleteAttachments($id)
	{
		if (Cache::has('MailTempleteAttachments', $id)) {
			return Cache::get('MailTempleteAttachments', $id);
		}
		$ids = (new \App\Db\Query())->select(['notesid'])->from('vtiger_senotesrel')
				->innerJoin('vtiger_crmentity', 'vtiger_senotesrel.notesid = vtiger_crmentity.crmid')
				->where(['vtiger_crmentity.deleted' => 0, 'vtiger_senotesrel.crmid' => $id])->column();
		$attachments = [];
		if ($ids) {
			$attachments['attachments'] = ['ids' => $ids];
		}
		Cache::save('MailTempleteAttachments', $id, $attachments, Cache::LONG);
		return $attachments;
	}
}
