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
	/** @var int Default smtp ID */
	public const SMTP_DEFAULT = 0;
	/** @var string Table name for configuration */
	public const TABLE_NAME_CONFIG = 'yetiforce_mail_config';

	/**
	 * Get smtp server by id.
	 *
	 * @param int $smtpId
	 *
	 * @return array
	 */
	public static function getSmtpById(int $smtpId): array
	{
		return static::getSmtpServers()[$smtpId] ?? [];
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

	public static function getSmtpServers(bool $skipDefault = false): array
	{
		$all = [];
		if (Cache::has('SmtpServers', 'all')) {
			$all = Cache::get('SmtpServers', 'all');
		} else {
			$dataReader = (new Db\Query())->from('s_#__mail_smtp')->createCommand(Db::getInstance('admin'))->query();
			while ($row = $dataReader->read()) {
				$all[$row['id']] = $row;
				if ($row['default']) {
					$all[self::SMTP_DEFAULT] = $row;
				}
			}
			ksort($all);
			Cache::save('SmtpServers', 'all', $all, Cache::LONG);
		}
		if ($skipDefault && !empty($all[self::SMTP_DEFAULT])) {
			unset($all[self::SMTP_DEFAULT]);
		}

		return $all;
	}

	/**
	 * Get default smtp ID.
	 *
	 * @return int
	 */
	public static function getDefaultSmtp()
	{
		return static::getSmtpById(static::SMTP_DEFAULT)['id'] ?? key(static::getSmtpServers());
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
	 * Get attr form send mail button.
	 *
	 * @param string      $email
	 * @param int|null    $record
	 * @param string|null $view
	 * @param string|null $type
	 *
	 * @return string
	 */
	public static function getComposeAttr(string $email, ?int $record = null, ?string $view = null, ?string $type = null): string
	{
		$return = '';
		foreach ([
			'email' => $email,
			'record' => $record,
			'view' => $view,
			'type' => $type,
		] as $key => $value) {
			if (null !== $value) {
				$return .= 'data-' . $key . '="' . Purifier::encodeHtml($value) . '" ';
			}
		}
		return $return;
	}

	/**
	 * Get user composer.
	 *
	 * @return string
	 */
	public static function getMailComposer(): string
	{
		if (Cache::staticHas('MailMailComposer')) {
			return Cache::staticGet('MailMailComposer');
		}
		$composer = \App\User::getCurrentUserModel()->getDetail('internal_mailer');
		if (!\Config\Main::$isActiveSendingMails || 1 == $composer || 'Base' !== $composer && ($composerInstance = self::getComposerInstance($composer)) && !$composerInstance->isActive()) {
			$composer = 'Base';
		}
		Cache::staticSave('MailMailComposer', '', $composer);
		return $composer;
	}

	/**
	 * Get composer instance.
	 *
	 * @param string $name
	 *
	 * @return \App\Mail\Composers\Base|null
	 */
	public static function getComposerInstance(string $name): ?Mail\Composers\Base
	{
		if (Cache::staticHas('MailComposerInstance', $name)) {
			return Cache::staticGet('MailComposerInstance', $name);
		}
		$className = '\App\Mail\Composers\\' . $name;
		if (!class_exists($className)) {
			\App\Log::warning('Not found composer class: ' . $className);
			return null;
		}
		$composer = new $className();
		Cache::staticSave('MailComposerInstance', $name, $composer);
		return $composer;
	}

	/**
	 * Check if the user has access to the internal mail client.
	 *
	 * @return bool
	 */
	public static function checkInternalMailClient(): bool
	{
		return 'InternalClient' === self::getMailComposer();
	}

	/**
	 * Get mail configuration by type.
	 *
	 * @param string $type
	 * @param string $field
	 *
	 * @return string|array
	 */
	public static function getConfig(string $type, string $field = '')
	{
		if (Cache::has('MailConfiguration', $type)) {
			$config = Cache::get('MailConfiguration', $type);
		} else {
			$config = (new \App\Db\Query())->from(self::TABLE_NAME_CONFIG)->indexBy('name')->where(['type' => $type])->all();
			Cache::save('MailConfiguration', $type, $config);
		}
		return $field ? $config[$field]['value'] ?? '' : $config;
	}

	/**
	 * Get signatures.
	 *
	 * @return array
	 */
	public static function getSignatures(): array
	{
		if (Cache::has('MailSignatures', 'all')) {
			$rows = Cache::get('MailSignatures', 'all');
		} else {
			$rows = (new \App\Db\Query())->from('s_#__mail_signature')->indexBy('name')->where(['status' => 1])->all();
			Cache::save('MailSignatures', 'all', $rows);
		}
		return $rows;
	}
}
