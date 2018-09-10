<?php

namespace App;

/**
 * Mail basic class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Mail
{
	/**
	 * Get smtp server by id.
	 *
	 * @param int $smtpId
	 *
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
	 * Get a list of all smtp servers.
	 *
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
	 * Get default smtp Id.
	 *
	 * @return int
	 */
	public static function getDefaultSmtp()
	{
		if (Cache::has('DefaultSmtp', '')) {
			return Cache::get('DefaultSmtp', '');
		}
		$id = (new Db\Query())->select(['id'])->from('s_#__mail_smtp')->where(['default' => 1])->scalar(Db::getInstance('admin'));
		if (!$id) {
			$id = (new Db\Query())->select(['id'])->from('s_#__mail_smtp')->limit(1)->scalar(Db::getInstance('admin'));
		}
		Cache::save('DefaultSmtp', '', $id, Cache::LONG);

		return $id;
	}

	/**
	 * Get templte list for module.
	 *
	 * @param string|bool $moduleName
	 * @param string|bool $type
	 * @param bool        $hideSystem
	 *
	 * @return array
	 */
	public static function getTempleteList($moduleName = false, $type = false, $hideSystem = true)
	{
		$cacheKey = "$moduleName.$type";
		if (Cache::has('MailTempleteList', $cacheKey)) {
			return Cache::get('MailTempleteList', $cacheKey);
		}
		$query = (new \App\Db\Query())->select(['name' => 'u_#__emailtemplates.name', 'id' => 'u_#__emailtemplates.emailtemplatesid', 'moduleName' => 'u_#__emailtemplates.module'])->from('u_#__emailtemplates')
			->innerJoin('vtiger_crmentity', 'u_#__emailtemplates.emailtemplatesid = vtiger_crmentity.crmid')
			->where(['vtiger_crmentity.deleted' => 0]);
		if ($moduleName) {
			$query->andWhere(['u_#__emailtemplates.module' => $moduleName]);
		}
		if ($type) {
			$query->andWhere(['u_#__emailtemplates.email_template_type' => $type]);
		}
		if ($hideSystem) {
			$query->andWhere(['u_#__emailtemplates.sys_name' => null]);
		}
		$row = $query->all();
		Cache::save('MailTempleteList', $cacheKey, $row, Cache::LONG);

		return $row;
	}

	/**
	 * Get mail template.
	 *
	 * @param int|string $id
	 *
	 * @return array
	 */
	public static function getTemplete($id)
	{
		$detail = static::getTempleteDetail($id);
		if (!$detail) {
			return false;
		}
		return array_merge(
			$detail, static::getAttachmentsFromTemplete($detail['emailtemplatesid'])
		);
	}

	/**
	 * Get mail template detail.
	 *
	 * @param int|string $id
	 *
	 * @return array
	 */
	public static function getTempleteDetail($id)
	{
		if (Cache::has('MailTempleteDetail', $id)) {
			return Cache::get('MailTempleteDetail', $id);
		}
		$query = (new \App\Db\Query())->from('u_#__emailtemplates')
			->innerJoin('vtiger_crmentity', 'u_#__emailtemplates.emailtemplatesid = vtiger_crmentity.crmid')
			->where(['vtiger_crmentity.deleted' => 0]);
		if (is_numeric($id)) {
			$query->andWhere(['u_#__emailtemplates.emailtemplatesid' => $id]);
		} else {
			$query->andWhere(['u_#__emailtemplates.sys_name' => $id]);
		}
		$row = $query->one();
		Cache::save('MailTempleteDetail', $id, $row, Cache::LONG);

		return $row;
	}

	/**
	 * Get attachments email template.
	 *
	 * @param int|string $id
	 *
	 * @return array
	 */
	public static function getAttachmentsFromTemplete($id)
	{
		if (Cache::has('MailAttachmentsFromTemplete', $id)) {
			return Cache::get('MailAttachmentsFromTemplete', $id);
		}
		$ids = (new \App\Db\Query())->select(['u_#__documents_emailtemplates.crmid'])->from('u_#__documents_emailtemplates')
			->innerJoin('vtiger_crmentity', 'u_#__documents_emailtemplates.relcrmid = vtiger_crmentity.crmid')
			->where(['vtiger_crmentity.deleted' => 0, 'u_#__documents_emailtemplates.relcrmid' => $id])->column();
		$attachments = [];
		if ($ids) {
			$attachments['attachments'] = ['ids' => $ids];
		}
		Cache::save('MailAttachmentsFromTemplete', $id, $attachments, Cache::LONG);

		return $attachments;
	}

	/**
	 * Get attachments from document.
	 *
	 * @param int|int[] $ids
	 *
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
			$name = Purifier::decodeHtml($row['name']);
			$filePath = realpath(ROOT_DIRECTORY . DIRECTORY_SEPARATOR . $row['path'] . $row['attachmentsid']);
			if (is_file($filePath)) {
				$attachments[$filePath] = $name;
			}
		}
		Cache::save('MailAttachmentsFromDocument', $cacheId, $attachments, Cache::LONG);

		return $attachments;
	}
}
