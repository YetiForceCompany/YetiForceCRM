<?php

/**
 * SocialMedia class.
 *
 * @package   App
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
	private function __construct(string $type)
	{
		$this->getConfig($type);
	}

	/**
	 * Get configuration from DB.
	 *
	 * @param string $type Type of config
	 *
	 * @throws \App\Exceptions\AppException
	 *
	 * @return array
	 */
	public function getConfig(string $type)
	{
		if (\App\Cache::has('SocialMediaConfig', $type)) {
			return $this->value = \App\Cache::get('SocialMediaConfig', $type);
		}
		$this->type = $type;
		$this->value = [];
		$dataReader = (new \App\Db\Query())
			->select(['name', 'value'])
			->from('u_#__social_media_config')
			->where(['type' => $type])
			->createCommand()
			->query();
		while ($row = $dataReader->read()) {
			$this->value[$row['name']] = \App\Json::decode($row['value']);
		}
		$dataReader->close();
		\App\Cache::save('SocialMediaConfig', $type, $this->value, \App\Cache::LONG);
		return $this->value;
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
		\App\Cache::delete('SocialMediaConfig', $this->type);
	}

	/**
	 * Checking whether social media are available for the module.
	 *
	 * @param string $moduleName Name of module
	 *
	 * @return bool
	 */
	public static function isEnableForModule(string $moduleName)
	{
		$socialMediaConfig = \AppConfig::module($moduleName, 'enable_social');
		if (false === $socialMediaConfig || empty($socialMediaConfig)) {
			return false;
		}
		if (!is_array($socialMediaConfig)) {
			throw new \App\Exceptions\AppException("ERR_ILLEGAL_VALUE||$moduleName:ENABLE_SOCIAL");
		}
		foreach ($socialMediaConfig as $socialMediaType) {
			if (in_array($socialMediaType, static::ALLOWED_UITYPE)) {
				return true;
			}
		}
		return false;
	}

	/**
	 * @param int    $uiType
	 * @param string $uiType
	 *
	 * @throws \App\Exceptions\AppException
	 *
	 * @return \App\SocialMedia\AbstractSocialMedia|bool
	 */
	public static function createObjectByUiType(int $uiType, string $accountName)
	{
		$className = static::getClassNameByUitype($uiType);
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
	public static function getClassNameByUitype(int $uiType)
	{
		if (isset(static::ALLOWED_UITYPE[$uiType])) {
			return __NAMESPACE__ . '\\' . ucfirst(static::ALLOWED_UITYPE[$uiType]);
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
	public static function isConfigured(int $uiType)
	{
		return call_user_func(static::getClassNameByUitype($uiType) . '::isConfigured');
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
		call_user_func(static::getClassNameByUitype($uiType) . '::log', $typeOfLog, $message);
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
			if (!\in_array($val, static::ALLOWED_UITYPE)) {
				throw new \App\Exceptions\AppException('ERR_NOT_ALLOWED_VALUE');
			}
			$arrUitype[] = $val;
		}
		return $arrUitype;
	}

	/**
	 * Remove social media account from database.
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
	 * @return \App\SocialMedia\AbstractSocialMedia|\Generator|void
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
	 * @param null|string|string[] $socialMediaType - if null then all
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
			->where(['uitype' => static::getUitypeFromParam($socialMediaType)])
			->andWhere(['presence' => [0, 2]])
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
