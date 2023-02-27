<?php
/**
 * Mail record finder file.
 *
 * @package App
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

namespace App\Mail;

/**
 * Mail record finder class.
 */
class RecordFinder
{
	/** @var array Cache. */
	private static $cache = [];

	/** @var array fields by module */
	private $fields = [];

	/**
	 * Get instance.
	 *
	 * @return self
	 */
	public static function getInstance(): self
	{
		return new self();
	}

	/**
	 * Set fields.
	 *
	 * @param array $fields fields by module
	 *
	 * @example ['Contacts' => ['email1','email2']]
	 *
	 * @return self
	 */
	public function setFields(array $fields): self
	{
		$this->fields = $fields;
		return $this;
	}

	/**
	 * Find record ids by email addresses.
	 *
	 * @param string|array $emails
	 * @param array        $modulesFields
	 *
	 * @return array
	 */
	public function findByEmail($emails): array
	{
		$idByEmail = [];

		if (!empty($emails)) {
			if (!\is_array($emails)) {
				$emails = explode(',', $emails);
			}
			foreach ($this->fields as $module => $fields) {
				$idByEmail = array_replace_recursive($idByEmail, $this->findByFields($module, $fields, $emails));
			}
		}

		return $idByEmail;
	}

	/**
	 * Find record ids.
	 *
	 * @param string $moduleName
	 * @param array  $fields
	 * @param array  $searchValue
	 * @param bool   $reload
	 *
	 * @return array
	 */
	public function findByFields(string $moduleName, array $fields, array $searchValue, bool $reload = false): array
	{
		$return = [];
		$cache = $moduleName . ':' . implode('|', $fields);
		if ($reload) {
			unset(self::$cache[$cache]);
		} else {
			foreach ($searchValue as $i => $value) {
				if (isset(self::$cache[$cache][$value])) {
					$return[$value] = self::$cache[$cache][$value];
					$return = array_filter($return);
					unset($searchValue[$i]);
				} else {
					self::$cache[$cache][$value] = [];
				}
			}
		}

		$moduleModel = \Vtiger_Module_Model::getInstance($moduleName);
		if ($searchValue && ($fields = array_filter($fields, fn ($name) => ($fieldsModel = $moduleModel->getFieldByName($name)) && $fieldsModel->isActiveField()))) {
			$dataReader = $this->getQueryForFields($moduleName, $fields, $searchValue)->createQuery()->createCommand()->query();
			while ($row = $dataReader->read()) {
				foreach ($fields as $fieldName) {
					$fieldModel = $moduleModel->getFieldByName($fieldName);
					$rowValue = $row[$fieldName];
					$recordId = $row['id'];
					switch ($fieldModel->getFieldDataType()) {
						case 'multiDomain':
							$rowDomains = $rowValue ? array_filter(explode(',', $rowValue)) : [];
							foreach ($searchValue as $email) {
								$domain = mb_strtolower(explode('@', $email)[1]);
								if (\in_array($domain, $rowDomains)) {
									self::$cache[$cache][$email][$recordId] = $return[$email][$recordId] = $recordId;
								}
							}
							break;
						case 'multiEmail':
							$rowEmails = $rowValue ? \App\Json::decode($rowValue) : [];
							foreach ($rowEmails as $emailData) {
								$email = $emailData['e'];
								if (\in_array($email, $searchValue)) {
									self::$cache[$cache][$email][$recordId] = $return[$email][$recordId] = $recordId;
								}
							}
							break;
						case 'recordNumber':
						default:
							if (\in_array($rowValue, $searchValue)) {
								self::$cache[$cache][$rowValue][$recordId] = $return[$rowValue][$recordId] = $recordId;
							}
							break;
					}
				}
			}
		}

		return $return;
	}

	/**
	 * Get query object.
	 *
	 * @param string $moduleName
	 * @param array  $fields
	 * @param array  $conditions
	 *
	 * @return \App\QueryGenerator
	 */
	public function getQueryForFields(string $moduleName, array $fields, array $conditions): \App\QueryGenerator
	{
		$queryGenerator = new \App\QueryGenerator($moduleName);
		$queryGenerator->setFields(array_merge(['id'], $fields));
		$queryGenerator->permissions = false;

		foreach ($fields as $fieldName) {
			$fieldModel = $queryGenerator->getModuleField($fieldName);
			switch ($fieldModel->getFieldDataType()) {
					case 'multiDomain':
						$domains = array_map(fn ($email) => mb_strtolower(explode('@', $email)[1]), $conditions);
						$queryGenerator->addCondition($fieldName, $domains, 'e', false);
						break;
					case 'multiEmail':
					case 'recordNumber':
					default:
						$queryGenerator->addCondition($fieldName, $conditions, 'e', false);
						break;
				}
		}

		return $queryGenerator;
	}

	/**
	 * Find email address.
	 *
	 * @param string $subject
	 * @param array  $modulesFields
	 *
	 * @return array
	 */
	public function findBySubject($subject): array
	{
		$records = [];
		foreach ($this->fields as $moduleName => $fields) {
			if ($fields && ($numbers = self::getRecordNumberFromString($subject, $moduleName, true))) {
				$records = array_replace_recursive($records, $this->findByFields($moduleName, $fields, (array) $numbers));
			}
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
