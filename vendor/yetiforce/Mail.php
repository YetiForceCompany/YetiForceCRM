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
		$all = (new Db\Query())->from('s_#__mail_smtp')->indexBy('id')->all(Db::getInstance('admin'));
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

	/**
	 * Get default smtp Id
	 * @return int
	 */
	public static function getDefaultSmtp()
	{
		if (Cache::has('DefaultSmtp', '')) {
			return Cache::get('DefaultSmtp', '');
		}
		$id = (new Db\Query())->select(['id'])->from('s_#__mail_smtp')->where(['default' => 1])->scalar(Db::getInstance('admin'));
		Cache::save('DefaultSmtp', '', $id, Cache::LONG);
		return $id;
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
			$detail, static::getAttachmentsFromTemplete($detail['ossmailtemplatesid'])
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
	 * Get attachments email template
	 * @param int|string $id
	 * @return array
	 */
	public static function getAttachmentsFromTemplete($id)
	{
		if (Cache::has('MailAttachmentsFromTemplete', $id)) {
			return Cache::get('MailAttachmentsFromTemplete', $id);
		}
		$ids = (new \App\Db\Query())->select(['notesid'])->from('vtiger_senotesrel')
				->innerJoin('vtiger_crmentity', 'vtiger_senotesrel.notesid = vtiger_crmentity.crmid')
				->where(['vtiger_crmentity.deleted' => 0, 'vtiger_senotesrel.crmid' => $id])->column();
		$attachments = [];
		if ($ids) {
			$attachments['attachments'] = ['ids' => $ids];
		}
		Cache::save('MailAttachmentsFromTemplete', $id, $attachments, Cache::LONG);
		return $attachments;
	}

	/**
	 * Get attachments from document
	 * @param int|int[] $ids
	 * @return array
	 */
	public static function getAttachmentsFromDocument($ids)
	{
		$cacheId = is_array($ids) ? implode(',', $ids) : $ids;
		if (Cache::has('MailAttachmentsFromDocument', $cacheId)) {
			return Cache::get('MailAttachmentsFromDocument', $cacheId);
		}
		$query = (new \App\Db\Query())->select(['vtiger_attachments.*'])->from('vtiger_attachments')
			->innerJoin('vtiger_crmentity', 'vtiger_attachments.attachmentsid = vtiger_crmentity.crmid')
			->innerJoin('vtiger_seattachmentsrel', 'vtiger_attachments.attachmentsid = vtiger_seattachmentsrel.attachmentsid')
			->where(['vtiger_crmentity.deleted' => 0, 'vtiger_seattachmentsrel.crmid' => $ids]);
		$attachments = [];
		$dataReader = $query->createCommand()->query();
		while ($row = $dataReader->read()) {
			$name = decode_html($row['name']);
			$filePath = realpath(ROOT_DIRECTORY . DIRECTORY_SEPARATOR . $row['path'] . $row['attachmentsid'] . '_' . $name);
			if (is_file($filePath)) {
				$attachments[$filePath] = $name;
			}
		}
		Cache::save('MailAttachmentsFromDocument', $cacheId, $attachments, Cache::LONG);
		return $attachments;
	}
}
