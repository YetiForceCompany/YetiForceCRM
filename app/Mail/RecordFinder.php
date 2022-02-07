<?php
/**
 * Mail record finder file.
 *
 * @package App
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace App\Mail;

/**
 * Mail record finder class.
 */
class RecordFinder
{
	/**
	 * Emails cache.
	 *
	 * @var array
	 */
	private static $emailsCache = [];
	/**
	 * Domain cache.
	 *
	 * @var array
	 */
	private static $domainCache = [];
	/**
	 * Record number cache.
	 *
	 * @var array
	 */
	private static $recordNumberCache = [];

	/**
	 * Find email address.
	 *
	 * @param mixed $emails
	 * @param array $modulesFields
	 *
	 * @return array
	 */
	public static function findByEmail($emails, array $modulesFields): array
	{
		if (empty($emails)) {
			return [];
		}
		if (!\is_array($emails)) {
			$emails = explode(',', $emails);
		}
		$ids = [];
		foreach ($modulesFields as $module => $fieldsByType) {
			foreach ($fieldsByType as $uiType => $fields) {
				if (319 === $uiType) {
					$ids = array_replace_recursive($ids, static::findByDomainField($module, $fields, $emails));
				} else {
					$ids = array_replace_recursive($ids, static::findByEmailField($module, $fields, $emails));
				}
			}
		}
		return $ids;
	}

	/**
	 * Search crm ids by emails field and module name.
	 *
	 * @param string   $moduleName
	 * @param string[] $fields
	 * @param string[] $emails
	 *
	 * @return array
	 */
	public static function findByEmailField(string $moduleName, array $fields, array $emails): array
	{
		$activeFields = $conditions = $return = [];
		foreach ($emails as $i => $email) {
			if (isset(self::$emailsCache[$moduleName][$email])) {
				$return[$email] = self::$emailsCache[$moduleName][$email];
				unset($emails[$i]);
			}
		}
		$queryGenerator = new \App\QueryGenerator($moduleName);
		$queryGenerator->permissions = false;
		foreach ($fields as $field) {
			if ($fieldsModel = $queryGenerator->getModuleField($field)) {
				$activeFields[] = $field;
				$conditions[] = [$fieldsModel->getColumnName() => $emails];
			}
		}
		if (!$activeFields) {
			return [];
		}
		if ($emails) {
			$queryGenerator->setFields(array_merge(['id'], $activeFields));
			$query = $queryGenerator->createQuery();
			$query->andWhere(array_merge(['or'], $conditions));
			$dataReader = $query->createCommand()->query();
			while ($row = $dataReader->read()) {
				foreach ($activeFields as $field) {
					$rowEmail = $row[$field];
					if (\in_array($rowEmail, $emails)) {
						self::$emailsCache[$moduleName][$rowEmail][] = $return[$rowEmail][] = $row['id'];
						unset($emails[array_search($rowEmail, $emails)]);
					}
				}
			}
			foreach ($emails as $i => $email) {
				self::$emailsCache[$moduleName][$email] = $return[$email] = [];
			}
		}
		return $return;
	}

	/**
	 * Search crm ids by domains field and module name.
	 *
	 * @param string   $moduleName
	 * @param string[] $fields
	 * @param string[] $emails
	 *
	 * @return array
	 */
	public static function findByDomainField(string $moduleName, array $fields, array $emails): array
	{
		$return = $activeFields = $domainsAndEmails = [];
		foreach ($emails as $email) {
			$domainsAndEmails[mb_strtolower(explode('@', $email)[1])][] = $email;
		}
		$domains = array_keys($domainsAndEmails);
		$queryGenerator = new \App\QueryGenerator($moduleName);
		$queryGenerator->permissions = false;
		foreach ($fields as $field) {
			if ($queryGenerator->getModuleField($field)) {
				$activeFields[] = $field;
				foreach ($domains as $domain) {
					$queryGenerator->addCondition($field, $domain, 'a', false);
				}
			}
		}
		if ($activeFields) {
			$queryGenerator->setFields(array_merge(['id'], $activeFields));
			$dataReader = $queryGenerator->createQuery()->createCommand()->query();
			while ($row = $dataReader->read()) {
				foreach ($activeFields as $field) {
					$rowDomains = $row[$field];
					$rowDomains = $rowDomains ? explode(',', trim($rowDomains, ',')) : [];
					if ($intersectRows = array_intersect($domains, $rowDomains)) {
						foreach ($intersectRows as $intersectRow) {
							if (isset($domainsAndEmails[$intersectRow])) {
								foreach ($domainsAndEmails[$intersectRow] as $email) {
									self::$domainCache[$moduleName][$email][] = $return[$email][] = $row['id'];
									unset($emails[array_search($email, $emails)]);
								}
							}
						}
					}
				}
			}
			foreach ($emails as $email) {
				self::$domainCache[$moduleName][$email] = $return[$email] = [];
			}
		}
		return $return;
	}

	/**
	 * Find email address.
	 *
	 * @param string $subject
	 * @param array  $modulesFields
	 *
	 * @return array
	 */
	public static function findBySubject($subject, array $modulesFields): array
	{
		$records = [];
		foreach ($modulesFields as $moduleName => $fields) {
			$numbers = self::getRecordNumberFromString($subject, $moduleName, true);
			if (!$numbers || !$fields) {
				continue;
			}
			$records = array_merge($records, \App\Utils::flatten(self::findByRecordNumber($numbers, $moduleName, \App\Utils::flatten($fields))));
		}
		return $records;
	}

	/**
	 * Gets the prefix from text.
	 *
	 * @param string $value
	 * @param string $moduleName
	 * @param bool   $multi
	 *
	 * @return bool|string|array
	 */
	public static function getRecordNumberFromString(string $value, string $moduleName, bool $multi = false)
	{
		$moduleData = \App\Fields\RecordNumber::getInstance($moduleName);
		$prefix = str_replace(['\{\{YYYY\}\}', '\{\{YY\}\}', '\{\{MM\}\}', '\{\{DD\}\}', '\{\{M\}\}', '\{\{D\}\}'], ['\d{4}', '\d{2}', '\d{2}', '\d{2}', '\d{1,2}', '\d{1,2}'], preg_quote($moduleData->get('prefix'), '/'));
		$postfix = str_replace(['\{\{YYYY\}\}', '\{\{YY\}\}', '\{\{MM\}\}', '\{\{DD\}\}', '\{\{M\}\}', '\{\{D\}\}'], ['\d{4}', '\d{2}', '\d{2}', '\d{2}', '\d{1,2}', '\d{1,2}'], preg_quote($moduleData->get('postfix'), '/'));
		$redex = preg_replace_callback('/\\\\{\\\\{picklist\\\\:([a-z0-9_]+)\\\\}\\\\}/i', function ($matches) {
			$picklistPrefix = array_column(\App\Fields\Picklist::getValues($matches[1]), 'prefix');
			if (!$picklistPrefix) {
				return '';
			}
			return '((' . implode('|', $picklistPrefix) . ')*)';
		}, '/\[' . $prefix . '([0-9]*)' . $postfix . '\]/');
		if ($multi) {
			preg_match_all($redex, $value, $match);
			if (!empty($match[0])) {
				$return = [];
				foreach ($match[0] as $row) {
					$return[] = trim($row, '[,]');
				}
				return $return;
			}
		} else {
			preg_match($redex, $value, $match);
			if (!empty($match)) {
				return trim($match[0], '[,]');
			}
		}
		return false;
	}

	/**
	 * Find record by sequence number field.
	 *
	 * @param array  $numbers
	 * @param string $moduleName
	 * @param array  $fields
	 *
	 * @return array
	 */
	public static function findByRecordNumber(array $numbers, string $moduleName, array $fields): array
	{
		$return = [];
		foreach ($numbers as $i => $number) {
			if (isset(self::$recordNumberCache[$moduleName][$number])) {
				$return[$number] = self::$recordNumberCache[$moduleName][$number];
				unset($numbers[$i]);
			}
		}
		if ($numbers) {
			$queryGenerator = new \App\QueryGenerator($moduleName);
			$queryGenerator->setFields(array_merge(['id'], $fields));
			$queryGenerator->permissions = false;
			foreach ($fields as $fieldName) {
				$queryGenerator->addCondition($fieldName, $numbers, 'e', false);
			}
			$queryGenerator->setOrder('id', 'DESC');
			$dataReader = $queryGenerator->createQuery()->createCommand()->query();
			while ($row = $dataReader->read()) {
				foreach ($fields as $fieldName) {
					$number = $row[$fieldName];
					self::$recordNumberCache[$moduleName][$number] = $return[$number][] = $row['id'];
				}
			}
		}
		return $return;
	}

	/**
	 * Find user email.
	 *
	 * @param array $emails
	 *
	 * @return string[]
	 */
	public static function findUserEmail(array $emails): array
	{
		foreach ($emails as $key => $email) {
			if (!\Users_Module_Model::checkMailExist($email)) {
				unset($emails[$key]);
			}
		}
		return $emails;
	}
}
