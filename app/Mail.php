<?php

namespace App;

/**
 * Mail basic class.
 *
 * @package App
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
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
	public static function getSmtpById(int $smtpId): array
	{
		if (Cache::has('SmtpServer', $smtpId)) {
			return Cache::get('SmtpServer', $smtpId);
		}
		$servers = static::getAll();
		$smtp = [];
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
	 * Get template list for module.
	 *
	 * @param string   $moduleName
	 * @param string   $type
	 * @param bool     $hideSystem
	 * @param int|null $userId
	 *
	 * @return array
	 */
	public static function getTemplateList(string $moduleName = '', string $type = '', bool $hideSystem = true, ?int $userId = null)
	{
		$queryGenerator = new \App\QueryGenerator('EmailTemplates', $userId ?? \App\User::getCurrentUserId());
		$queryGenerator->setFields(['id', 'name', 'module_name']);
		if ($moduleName) {
			$queryGenerator->addCondition('module_name', $moduleName, 'e');
		}
		if ($type) {
			$queryGenerator->addCondition('email_template_type', $type, 'e');
		}
		if ($hideSystem) {
			$queryGenerator->addNativeCondition(['u_#__emailtemplates.sys_name' => [null, '']]);
		}
		return $queryGenerator->createQuery()->all();
	}

	/**
	 * Get mail template.
	 *
	 * @param int|string $id
	 * @param bool       $attachments
	 *
	 * @return array
	 */
	public static function getTemplate($id, bool $attachments = true): array
	{
		if (!is_numeric($id)) {
			$id = self::getTemplateIdFromSysName($id);
		}
		if (!$id || !\App\Record::isExists($id, 'EmailTemplates')) {
			return [];
		}
		$template = \Vtiger_Record_Model::getInstanceById($id, 'EmailTemplates');
		if (!$attachments) {
			return $template->getData();
		}
		return array_merge(
			$template->getData(), static::getAttachmentsFromTemplate($template->getId())
		);
	}

	/**
	 * Get template ID.
	 *
	 * @param string $name
	 *
	 * @return int|null
	 */
	public static function getTemplateIdFromSysName(string $name): ?int
	{
		$cacheName = 'TemplateIdFromSysName';
		if (Cache::has($cacheName, '')) {
			$templates = Cache::get($cacheName, '');
		} else {
			$queryGenerator = new \App\QueryGenerator('EmailTemplates');
			$queryGenerator->setFields(['id']);
			$queryGenerator->permissions = false;
			$queryGenerator->addNativeCondition(['not', ['sys_name' => null]]);
			$templates = $queryGenerator->createQuery()->select(['sys_name', 'emailtemplatesid'])->createCommand()->queryAllByGroup();
			Cache::save($cacheName, '', $templates, Cache::LONG);
		}
		return $templates[$name] ?? null;
	}

	/**
	 * Get attachments email template.
	 *
	 * @param int|string $id
	 *
	 * @return array
	 */
	public static function getAttachmentsFromTemplate($id)
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
	 * @param mixed     $returnOnlyName
	 *
	 * @return array
	 */
	public static function getAttachmentsFromDocument($ids, $returnOnlyName = true)
	{
		$cacheId = "$returnOnlyName|" . \is_array($ids) ? implode(',', $ids) : $ids;
		if (Cache::has('MailAttachmentsFromDocument', $cacheId)) {
			return Cache::get('MailAttachmentsFromDocument', $cacheId);
		}
		$query = (new \App\Db\Query())->select(['vtiger_attachments.*'])->from('vtiger_attachments')
			->innerJoin('vtiger_seattachmentsrel', 'vtiger_attachments.attachmentsid = vtiger_seattachmentsrel.attachmentsid')
			->where(['vtiger_seattachmentsrel.crmid' => $ids]);
		$attachments = [];
		$dataReader = $query->createCommand()->query();
		while ($row = $dataReader->read()) {
			$filePath = realpath(ROOT_DIRECTORY . \DIRECTORY_SEPARATOR . $row['path'] . $row['attachmentsid']);
			if (is_file($filePath)) {
				$attachments[$filePath] = $returnOnlyName ? Purifier::decodeHtml($row['name']) : $row;
			}
		}
		Cache::save('MailAttachmentsFromDocument', $cacheId, $attachments, Cache::LONG);
		return $attachments;
	}

	/**
	 * Check if the user has access to the mail client.
	 *
	 * @return bool
	 */
	public static function checkMailClient(): bool
	{
		if (Cache::staticHas('MailCheckMailClient')) {
			return Cache::staticGet('MailCheckMailClient');
		}
		$return = \Config\Main::$isActiveSendingMails && \App\Privilege::isPermitted('OSSMail');
		Cache::staticSave('MailCheckMailClient', '', $return);
		return $return;
	}

	/**
	 * Check if the user has access to the internal mail client.
	 *
	 * @return bool
	 */
	public static function checkInternalMailClient(): bool
	{
		if (Cache::staticHas('MailCheckInternalMailClient')) {
			return Cache::staticGet('MailCheckInternalMailClient');
		}
		$return = self::checkMailClient() && 1 === (int) \App\User::getCurrentUserModel()->getDetail('internal_mailer') && file_exists(ROOT_DIRECTORY . '/public_html/modules/OSSMail/roundcube/');
		Cache::staticSave('MailCheckInternalMailClient', '', $return);
		return $return;
	}
}
