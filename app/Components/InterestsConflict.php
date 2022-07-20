<?php

/**
 * Conflict of interest component file.
 *
 * @package App
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace App\Components;

/**
 * Conflict of interest component class.
 */
class InterestsConflict
{
	/** @var int */
	public const CONF_STATUS_CONFLICT_NO = 0;
	/** @var int */
	public const CONF_STATUS_CONFLICT_YES = 1;
	/** @var int */
	public const CONF_STATUS_CANCELED = 2;
	/** @var int */
	public const UNLOCK_STATUS_NEW = 0;
	/** @var int */
	public const UNLOCK_STATUS_ACCEPTED = 1;
	/** @var int */
	public const UNLOCK_STATUS_REJECTED = 2;
	/** @var int */
	public const UNLOCK_STATUS_CANCELED = 3;
	/** @var int */
	public const CHECK_STATUS_INACTIVE = 0;
	/** @var int */
	public const CHECK_STATUS_CONFIRMATION = 1;
	/** @var int */
	public const CHECK_STATUS_NO_CONFLICT = 2;
	/** @var int */
	public const CHECK_STATUS_CONFLICT = 3;
	/** @var string[] */
	public const UNLOCK_STATUS_LABELS = [
		self::UNLOCK_STATUS_NEW => 'LBL_INTERESTS_CONFLICT_UNLOCK_STATUS_NEW',
		self::UNLOCK_STATUS_ACCEPTED => 'LBL_INTERESTS_CONFLICT_UNLOCK_STATUS_ACCEPTED',
		self::UNLOCK_STATUS_REJECTED => 'LBL_INTERESTS_CONFLICT_UNLOCK_STATUS_REJECTED',
		self::UNLOCK_STATUS_CANCELED => 'LBL_INTERESTS_CONFLICT_CONFIRM_CANCELED',
	];

	/**
	 * Check the conflict status.
	 *
	 * @param int    $record
	 * @param string $moduleName
	 *
	 * @return int
	 */
	public static function check(int $record, string $moduleName): int
	{
		if (empty(\Config\Components\InterestsConflict::$isActive) || empty(\Config\Components\InterestsConflict::$modules[$moduleName])) {
			return self::CHECK_STATUS_INACTIVE;
		}
		if (0 !== \App\ModuleHierarchy::getModuleLevel($moduleName)) {
			if ($parent = self::getParent($record, $moduleName)) {
				$record = $parent['id'];
			} else {
				\App\Log::warning("No parent record could be found |$record|$moduleName", __METHOD__);
				return self::CHECK_STATUS_INACTIVE;
			}
		}
		if (!($row = self::getLast($record))) {
			return self::CHECK_STATUS_CONFIRMATION;
		}
		if (0 !== \Config\Components\InterestsConflict::$confirmationTimeInterval && strtotime($row['date_time']) < strtotime('-' . \Config\Components\InterestsConflict::$confirmationTimeInterval)) {
			return self::CHECK_STATUS_CONFIRMATION;
		}
		return self::CONF_STATUS_CONFLICT_YES === $row['status'] ? self::CHECK_STATUS_CONFLICT : self::CHECK_STATUS_NO_CONFLICT;
	}

	/**
	 * Get the last conflict of interests information.
	 *
	 * @param int      $record
	 * @param int|null $userId
	 *
	 * @return array|null
	 */
	public static function getLast(int $record, ?int $userId = null): ?array
	{
		if (null === $userId) {
			$userId = \App\User::getCurrentUserRealId();
		}
		$row = null;
		foreach (self::getByRecord($record) as $value) {
			if ($value['user_id'] == $userId && self::CONF_STATUS_CANCELED != $value['status']) {
				$row = $value;
				break;
			}
		}
		return $row;
	}

	/**
	 * Get parent record id.
	 *
	 * @param int    $record
	 * @param string $moduleName
	 *
	 * @return array|null
	 */
	public static function getParent(int $record, string $moduleName): ?array
	{
		if (0 === \App\ModuleHierarchy::getModuleLevel($moduleName)) {
			return ['id' => $record, 'moduleName' => $moduleName];
		}
		$recordModel = \Vtiger_Record_Model::getInstanceById($record, $moduleName);
		foreach (\Config\Components\InterestsConflict::$modules[$moduleName] ?? [] as $item) {
			if ($recordModel->isEmpty($item['relatedFieldName'])) {
				continue;
			}
			$relatedModuleName = \App\Record::getType($recordModel->get($item['relatedFieldName']));
			if (empty($item['intermediateFieldName'])) {
				if ($relatedModuleName === $item['base']) {
					return ['id' => $recordModel->get($item['relatedFieldName']), 'moduleName' => $relatedModuleName];
				}
				continue;
			}
			if ($relatedModuleName !== $item['intermediate']) {
				continue;
			}
			$intermediateRecordModel = \Vtiger_Record_Model::getCleanInstance($relatedModuleName);
			$intermediateRecordModel->setId($recordModel->get($item['relatedFieldName']));
			$relatedId = $intermediateRecordModel->getValueByField($item['intermediateFieldName']);
			if (($relatedModuleName = \App\Record::getType($relatedId)) && $relatedModuleName === $item['base']) {
				return ['id' => $relatedId, 'moduleName' => $relatedModuleName];
			}
		}
		return null;
	}

	/**
	 * Get confirmation by record.
	 *
	 * @param int $record
	 *
	 * @return array
	 */
	public static function getByRecord(int $record): array
	{
		$cacheName = 'InterestsConflict::getByRecord';
		if (\App\Cache::has($cacheName, $record)) {
			return \App\Cache::get($cacheName, $record);
		}
		$row = (new \App\Db\Query())
			->from('u_#__interests_conflict_conf')
			->where(['related_id' => $record])
			->all();
		return \App\Cache::save($cacheName, $record, $row);
	}

	/**
	 *  Cancel user confirmation.
	 *
	 * @param int    $userId
	 * @param int    $record
	 * @param string $comment
	 *
	 * @return void
	 */
	public static function setCancel(int $userId, int $record, string $comment): void
	{
		$row = self::getLast($record, $userId);
		$userCreateCommand = \App\Db::getInstance()->createCommand();
		$userCreateCommand->update('u_#__interests_conflict_conf', [
			'status' => self::CONF_STATUS_CANCELED,
			'modify_date_time' => date('Y-m-d H:i:s'),
			'modify_user_id' => \App\User::getCurrentUserRealId(),
		], ['id' => $row['id']])
			->execute();
		\App\Cache::delete('InterestsConflict::getByRecord', $record);
		$userCreateCommand->insert('u_#__interests_conflict_unlock', [
			'date_time' => $row['date_time'],
			'status' => self::UNLOCK_STATUS_CANCELED,
			'user_id' => $row['user_id'],
			'related_id' => $row['related_id'],
			'source_id' => $row['source_id'],
			'comment' => $comment,
			'modify_date_time' => date('Y-m-d H:i:s'),
			'modify_user_id' => \App\User::getCurrentUserRealId(),
		])->execute();
	}

	/**
	 * Unlock access request.
	 *
	 * @param int    $baseRecord
	 * @param int    $sourceRecord
	 * @param string $comment
	 *
	 * @return void
	 */
	public static function unlock(int $baseRecord, int $sourceRecord, string $comment): void
	{
		\App\Db::getInstance()
			->createCommand()
			->insert('u_#__interests_conflict_unlock', [
				'date_time' => date('Y-m-d H:i:s'),
				'status' => self::UNLOCK_STATUS_NEW,
				'user_id' => \App\User::getCurrentUserRealId(),
				'related_id' => $baseRecord,
				'source_id' => $sourceRecord,
				'comment' => $comment,
			])->execute();
		if (\Config\Components\InterestsConflict::$sendMailAccessRequest) {
			\App\Mailer::sendFromTemplate([
				'template' => 'InterestsConflictAccessRequest',
				'to' => \Config\Components\InterestsConflict::$notificationsEmails,
				'dateTime' => date('Y-m-d H:i:s'),
				'user' => \App\User::getCurrentUserModel()->getName(),
				'record' => \App\Record::getHtmlLink($baseRecord),
				'comment' => nl2br($comment),
			]);
		}
	}

	/**
	 * User confirmation.
	 *
	 * @param int $baseRecord
	 * @param int $sourceRecord
	 * @param int $value
	 *
	 * @return void
	 */
	public static function confirmation(int $baseRecord, int $sourceRecord, int $value): void
	{
		$userCreateCommand = \App\Db::getInstance()->createCommand();
		$all = (new \App\Db\Query())
			->from('u_#__interests_conflict_conf')
			->where(['user_id' => \App\User::getCurrentUserRealId(), 'related_id' => $baseRecord])
			->all();
		$logCreateCommand = \App\Db::getInstance('log')->createCommand();
		foreach ($all as $row) {
			$id = $row['id'];
			unset($row['id']);
			$execute = $logCreateCommand->insert('b_#__interests_conflict_conf', $row)->execute();
			if ($execute) {
				$userCreateCommand->delete('u_#__interests_conflict_conf', ['id' => $id])->execute();
			}
		}
		$userCreateCommand->insert('u_#__interests_conflict_conf', [
			'date_time' => date('Y-m-d H:i:s'),
			'status' => $value,
			'user_id' => \App\User::getCurrentUserRealId(),
			'related_id' => $baseRecord,
			'related_label' => \App\Record::getLabel($baseRecord),
			'source_id' => $sourceRecord,
		])->execute();
		\App\Cache::delete('InterestsConflict::getByRecord', $baseRecord);
	}

	/**
	 * Update unlock status.
	 *
	 * @param int $id
	 * @param int $status
	 *
	 * @return void
	 */
	public static function updateUnlockStatus(int $id, int $status): void
	{
		\App\Db::getInstance()
			->createCommand()
			->update('u_#__interests_conflict_unlock', [
				'status' => $status,
				'modify_date_time' => date('Y-m-d H:i:s'),
				'modify_user_id' => \App\User::getCurrentUserRealId(),
			], ['id' => $id])
			->execute();
		$row = (new \App\Db\Query())->select(['related_id', 'user_id'])->from('u_#__interests_conflict_unlock')->where(['id' => $id])->one();
		if (self::UNLOCK_STATUS_ACCEPTED === $status) {
			if ($row) {
				\App\Db::getInstance()
					->createCommand()
					->update('u_#__interests_conflict_conf', [
						'status' => self::CONF_STATUS_CANCELED,
						'modify_date_time' => date('Y-m-d H:i:s'),
						'modify_user_id' => \App\User::getCurrentUserRealId(),
					], ['user_id' => $row['user_id'], 'related_id' => $row['related_id']])
					->execute();
				\App\Cache::delete('InterestsConflict::getByRecord', $row['related_id']);
			}
		}
		if (\Config\Components\InterestsConflict::$sendMailAccessResponse) {
			$userModel = \App\User::getUserModel($row['user_id']);
			\App\Mailer::sendFromTemplate([
				'template' => 'InterestsConflictAccessResponse',
				'moduleName' => 'Users',
				'recordId' => $row['user_id'],
				'to' => $userModel->getDetail('email1'),
				'record' => \App\Record::getHtmlLink($row['related_id']),
				'status' => \App\Language::translate(self::UNLOCK_STATUS_LABELS[$status], '_Base', $userModel->getDetail('language')),
			]);
		}
	}

	/**
	 * Get modules list.
	 *
	 * @return array
	 */
	public static function getModules(): array
	{
		$cacheName = 'InterestsConflict::getModules';
		if (\App\Cache::has($cacheName, '')) {
			return \App\Cache::get($cacheName, '');
		}
		$allModules = array_map(fn ($v) => 0 > $v ? 999 : sprintf('%03d', $v), array_column(\vtlib\Functions::getAllModules(false, true), 'tabsequence', 'name'));
		$excludedModules = ['ModComments'];
		$baseModules = $return = $modules = $baseModules = [];
		foreach (array_keys(\App\ModuleHierarchy::getModulesByLevel(0)) as $moduleName) {
			if (\App\Module::isModuleActive($moduleName)) {
				$baseModules[$moduleName] = $moduleName;
				$key = "$moduleName";
				$return["0000|$key"] = [
					'key' => $key,
					'base' => $moduleName,
					'target' => $moduleName,
					'value' => \App\Json::encode([
						'related' => $moduleName,
					]),
					'map' => \App\Language::translateSingularModuleName($moduleName),
				];
			}
		}
		$relatedFields = \App\Field::getRelatedFieldForModule();
		$keys = [];
		foreach ($relatedFields as $sourceModuleName => $forModules) {
			foreach ($forModules as $targetModuleName => $field) {
				if (isset($baseModules[$targetModuleName]) && $sourceModuleName !== $targetModuleName && !\in_array($sourceModuleName, $excludedModules)) {
					$key = "$sourceModuleName({$field['fieldname']})|{$targetModuleName}";
					$modules[$sourceModuleName][] = $return["{$allModules[$sourceModuleName]}|$allModules[$targetModuleName]|$key"] = [
						'key' => $key,
						'base' => $targetModuleName,
						'target' => $sourceModuleName,
						'value' => \App\Json::encode([
							'base' => $targetModuleName,
							'related' => $sourceModuleName,
							'relatedFieldName' => $field['fieldname'],
						]),
						'map' => \App\Language::translateSingularModuleName($targetModuleName) . ' << ' . \App\Language::translateSingularModuleName($sourceModuleName) . ' (' . \App\Language::translate($field['fieldlabel'], $sourceModuleName) . ')',
						'field' => $field,
					];
					$keys[$key] = "{$allModules[$sourceModuleName]}|$allModules[$targetModuleName]|$key";
				}
			}
		}
		foreach ($relatedFields as $sourceModuleName => $forModules) {
			foreach ($forModules as $targetModuleName => $field) {
				if (isset($modules[$targetModuleName]) && $sourceModuleName !== $targetModuleName && !\in_array($sourceModuleName, $excludedModules)) {
					foreach ($modules[$targetModuleName] as $parent) {
						$key = "{$sourceModuleName}({$field['fieldname']})|{$parent['key']}";
						$return["{$allModules[$sourceModuleName]}|{$allModules[$parent['base']]}|$key"] = [
							'key' => $key,
							'base' => $parent['base'],
							'target' => $sourceModuleName,
							'value' => \App\Json::encode([
								'base' => $parent['base'],
								'intermediate' => $targetModuleName,
								'intermediateFieldName' => $parent['field']['fieldname'],
								'related' => $sourceModuleName,
								'relatedFieldName' => $field['fieldname'],
							]),
							'map' => $parent['map'] . ' << ' . \App\Language::translateSingularModuleName($sourceModuleName) . ' (' . \App\Language::translate($field['fieldlabel'], $sourceModuleName) . ')',
						];
						$keys[$key] = "{$allModules[$sourceModuleName]}|{$allModules[$parent['base']]}|$key";
					}
				}
			}
		}
		ksort($return);
		$start = [];
		foreach (\Config\Components\InterestsConflict::$modules ?? [] as $rows) {
			foreach ($rows as $row) {
				if (isset($keys[$row['key']])) {
					$start[$keys[$row['key']]] = $return[$keys[$row['key']]];
					unset($return[$keys[$row['key']]]);
				}
			}
		}
		return \App\Cache::save($cacheName, '', array_merge($start, $return));
	}
}
