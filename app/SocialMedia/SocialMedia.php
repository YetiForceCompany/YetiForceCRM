<?php

/**
 * SocialMedia class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Arkadiusz Adach <a.adach@yetiforce.com>
 */

namespace App\SocialMedia;

/**
 * Class SocialMedia.
 *
 * @package App\SocialMedia
 */
class SocialMedia
{
	/**
	 * Array of allowed uiType.
	 */
	public const ALLOWED_UITYPE = [313 => 'twitter'];

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
	 * @return bool|string
	 */
	public static function getClassNameByUitype($uiType)
	{
		if (\in_array($uiType, static::ALLOWED_UITYPE)) {
			return __NAMESPACE__ . '\\' . ucfirst(\array_keys(static::ALLOWED_UITYPE, $uiType)[0]);
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
	public static function isConfigured($uiType)
	{
		return call_user_func(static::getClassNameByUitype($uiType) . '::isConfigured');
	}

	/**
	 * @param int    $uiType
	 * @param string $message
	 *
	 * @throws \App\Exceptions\AppException
	 */
	public static function log($uiType, $typeOfLog, $message)
	{
		call_user_func(static::getClassNameByUitype($uiType) . '::log', $typeOfLog, $message);
	}

	/**
	 * @param string|array|null $socialMediaType
	 *
	 * @throws \App\Exceptions\AppException
	 *
	 * @return int[]
	 */
	public static function getUitypeFromParam($socialMediaType = null)
	{
		if (!\is_array($socialMediaType)) {
			$socialMediaType = [$socialMediaType];
		}
		$arrUitype = [];
		foreach ($socialMediaType as $val) {
			if (!isset(static::ALLOWED_UITYPE[$val])) {
				throw new \App\Exceptions\AppException('ERR_NOT_ALLOWED_VALUE');
			}
			$arrUitype[$val] = static::ALLOWED_UITYPE[$val];
		}
		return $arrUitype;
	}

	/**
	 * Remove social media account from database.
	 *
	 * @param int    $uiType
	 * @param string $accountName
	 */
	public static function remove($uiType, $accountName)
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
	 * @return \App\Db\Query|null|void
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
		$dataReader = (new \App\Db\Query())->from('s_#__social_media_logs')
			->orderBy(['date_log' => SORT_DESC])
			->limit(1000)
			->createCommand()->query();
		while (($row = $dataReader->read())) {
			yield $row;
		}
	}
}
