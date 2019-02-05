<?php

/**
 * SocialMedia class.
 *
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Arkadiusz Adach <a.adach@yetiforce.com>
 */

namespace App;

/**
 * Class SocialMedia.
 *
 * @package App
 */
class SocialMedia extends Base
{
	/**
	 * Array of allowed uiType.
	 */
	public const ALLOWED_UITYPE = [313 => 'twitter'];

	/**
	 * Configuration type.
	 *
	 * @var string
	 */
	protected $type;

	/**
	 * Return object instance.
	 *
	 * @param string $type Type of config
	 *
	 * @return \App\SocialMedia
	 */
	public static function getInstance(string $type)
	{
		return new self($type);
	}

	/**
	 * SocialMedia constructor.
	 *
	 * @param string $type Type of config
	 */
	public function __construct(string $type)
	{
		$this->type = $type;
		if (Cache::has('SocialMediaConfig', $this->type)) {
			$this->value = Cache::get('SocialMediaConfig', $this->type);
			return;
		}
		$this->value = [];
		$dataReader = (new \App\Db\Query())
			->select(['name', 'value'])
			->from('u_#__social_media_config')
			->where(['type' => $this->type])
			->createCommand()
			->query();
		while ($row = $dataReader->read()) {
			$this->value[$row['name']] = \App\Json::decode($row['value']);
		}
		$dataReader->close();
		Cache::save('SocialMediaConfig', $this->type, $this->value, Cache::LONG);
	}

	/**
	 * Save changes to DB.
	 *
	 * @throws \yii\db\Exception
	 */
	public function save()
	{
		$db = \App\Db::getInstance();
		$transaction = $db->beginTransaction();
		try {
			foreach ($this->value as $key => $val) {
				$db->createCommand()->update('u_#__social_media_config',
					['value' => \App\Json::encode($val)],
					['type' => $this->type, 'name' => $key]
				)->execute();
			}
			$transaction->commit();
			$this->clearCache();
		} catch (\Exception $e) {
			$transaction->rollBack();
			throw $e;
		}
	}

	/**
	 * Function clears cache.
	 */
	public function clearCache()
	{
		Cache::delete('SocialMediaConfig', $this->type);
	}

	/**
	 * Checking whether social media are available for the module.
	 *
	 * @param string $moduleName Name of module
	 *
	 * @return bool
	 */
	public static function isEnableForModule(string $moduleName): bool
	{
		$returnVal = false;
		foreach (static::ALLOWED_UITYPE as $socialMediaType) {
			if (in_array($moduleName, \App\Config::component('Social', \strtoupper("{$socialMediaType}_ENABLE_FOR_MODULES"), []))) {
				$returnVal = true;
				break;
			}
		}
		return $returnVal;
	}

	/**
	 * Create object by uitype.
	 *
	 * @param int    $uiType
	 * @param string $accountName
	 *
	 * @throws \App\Exceptions\AppException
	 *
	 * @return \App\SocialMedia\Base|bool
	 */
	public static function createObjectByUiType(int $uiType, string $accountName)
	{
		$className = static::getClassNameByUiType($uiType);
		if ($className === false) {
			return false;
		}
		return new $className($accountName);
	}

	/**
	 * Get class name.
	 *
	 * @param int $uiType
	 *
	 * @throws \App\Exceptions\AppException
	 *
	 * @return string
	 */
	public static function getClassNameByUiType(int $uiType)
	{
		if (isset(static::ALLOWED_UITYPE[$uiType])) {
			return __NAMESPACE__ . '\\SocialMedia\\' . ucfirst(static::ALLOWED_UITYPE[$uiType]);
		}
		throw new \App\Exceptions\AppException('ERR_NOT_ALLOWED_VALUE');
	}

	/**
	 * Check if it is configured social media by uitype.
	 *
	 * @param int $uiType
	 *
	 * @throws \App\Exceptions\AppException
	 *
	 * @return bool
	 */
	public static function isActiveByType(int $uiType)
	{
		return call_user_func(static::getClassNameByUiType($uiType) . '::isActive');
	}

	/**
	 * Remove mass social media records from the database if not used.
	 *
	 * @param int      $uiType
	 * @param string[] $logins
	 *
	 * @throws \App\Exceptions\AppException
	 *
	 * @return mixed
	 */
	public static function removeMass(int $uiType, array $logins)
	{
		$loginsToRemove = static::getSocialMediaQuery([static::ALLOWED_UITYPE[$uiType]])
			->select(['account_name'])
			->where(['account_name' => $logins])
			->having(['=', 'count(*)', 1])->column();
		return call_user_func_array(static::getClassNameByUiType($uiType) . '::removeMass', [$loginsToRemove]);
	}

	/**
	 * @param int    $uiType
	 * @param string $typeOfLog
	 * @param string $message
	 *
	 * @throws \App\Exceptions\AppException
	 */
	public static function log(int $uiType, string $typeOfLog, string $message)
	{
		call_user_func(static::getClassNameByUiType($uiType) . '::log', $typeOfLog, $message);
	}

	/**
	 * @param string|string[] $socialMediaType
	 *
	 * @throws \App\Exceptions\AppException
	 *
	 * @return int[]
	 */
	public static function getUitypeFromParam($socialMediaType)
	{
		if (!\is_array($socialMediaType)) {
			$socialMediaType = [$socialMediaType];
		}
		$arrUitype = [];
		foreach ($socialMediaType as $val) {
			if (($arrUitype[] = \array_search($val, static::ALLOWED_UITYPE)) === false) {
				throw new \App\Exceptions\AppException('ERR_NOT_ALLOWED_VALUE');
			}
		}
		return $arrUitype;
	}

	/**
	 * Remove a social account from the database if not used.
	 *
	 * @param int    $uiType
	 * @param string $accountName
	 */
	public static function removeAccount(int $uiType, string $accountName)
	{
		$query = static::getSocialMediaQuery([static::ALLOWED_UITYPE[$uiType]])
			->where(['account_name' => $accountName])
			->having(['=', 'count(*)', 1]);
		if ($query->exists()) {
			$socialMedia = static::createObjectByUiType($uiType, $accountName);
			if ($socialMedia === false) {
				throw new \App\Exceptions\AppException('ERR_NOT_ALLOWED_VALUE');
			}
			$socialMedia->remove();
		}
	}

	/**
	 * Get all social media accounts.
	 *
	 * @param string|string[] $socialMediaType
	 *
	 * @throws \App\Exceptions\AppException
	 *
	 * @return \App\SocialMedia\Base|\Generator|void
	 */
	public static function getSocialMediaAccount($socialMediaType)
	{
		if (!$socialMediaType) {
			return;
		}
		$query = static::getSocialMediaQuery($socialMediaType);
		if ($query === false) {
			return;
		}
		$dataReader = $query->createCommand()->query();
		while (($row = $dataReader->read())) {
			yield static::createObjectByUiType((int) $row['uitype'], $row['account_name']);
		}
		$dataReader->close();
	}

	/**
	 * Get social media query.
	 *
	 * @param string|string[] $socialMediaType
	 *
	 * @throws \App\Exceptions\AppException
	 *
	 * @return \App\Db\Query|false
	 */
	private static function getSocialMediaQuery($socialMediaType)
	{
		$fields = (new \App\Db\Query())
			->select(['columnname', 'tablename', 'uitype'])
			->from('vtiger_field')
			->leftJoin('vtiger_tab', 'vtiger_field.tabid = vtiger_tab.tabid')
			->where(['vtiger_tab.presence' => 0])
			->andWhere(['vtiger_field.presence' => [0, 2]])
			->andWhere(['vtiger_field.uitype' => static::getUitypeFromParam($socialMediaType)])
			->all();
		if (!$fields) {
			return false;
		}
		$query = null;
		foreach ($fields as $i => $field) {
			$subQuery = (new \App\Db\Query())
				->select(['account_name' => $field['columnname'], 'uitype' => new \yii\db\Expression($field['uitype'])])
				->from($field['tablename'])
				->where(['not', [$field['columnname'] => null]])
				->andWhere(['not', [$field['columnname'] => '']]);
			if ($i === 0) {
				$query = $subQuery;
			} else {
				$query->union($subQuery, true);
			}
		}
		return (new \App\Db\Query())
			->select(['social.*', 'account_count' => new \yii\db\Expression('COUNT(*)')])
			->from(['social' => $query])
			->groupBy(['account_name', 'uitype']);
	}

	/**
	 * Get logs from db.
	 *
	 * @return \Generator
	 */
	public static function getLogs()
	{
		$dataReader = (new \App\Db\Query())->from('l_#__social_media_logs')
			->orderBy(['date' => SORT_DESC])
			->limit(1000)
			->createCommand()->query();
		while (($row = $dataReader->read())) {
			yield $row;
		}
		$dataReader->close();
	}
}
