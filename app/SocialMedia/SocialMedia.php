<?php

namespace App\SocialMedia;

class SocialMedia
{
	public const ALLOWED_UITYPE = ['twitter' => 313];

	public static function isAllowed($socialMediaType)
	{
		return isset(static::ALLOWED_UITYPE[$socialMediaType]);
	}

	/**
	 * Add account to database if not exists.
	 *
	 * @param string $login
	 * @param string $socialMediaType
	 *
	 * @throws \yii\db\Exception
	 */
	public static function addAccount($login, $socialMediaType)
	{
		if (static::isAllowed($socialMediaType)) {
			if (!(new \App\Db\Query())->from('u_#__social_media_accounts')->where(['type' => $socialMediaType])->andWhere(['login' => $login])->exists()) {
				\App\Db::getInstance()->createCommand()->insert('u_#__social_media_accounts', [
					'login' => $login, 'type' => $socialMediaType
				])->execute();
			}
		}
	}

	public static function createObjectByType($socialMediaType)
	{
		if (isset(static::ALLOWED_UITYPE[$socialMediaType])) {
			switch ($socialMediaType) {
				case 'twitter':
					return new Twitter();
					break;
			}
		}
		return false;
	}

	/**
	 * @param $uitype
	 *
	 * @throws \App\Exceptions\AppException
	 *
	 * @return \App\SocialMedia\SocialMediaInterface|bool
	 */
	public static function createObjectByUiType($uitype)
	{
		if (\in_array($uitype, static::ALLOWED_UITYPE)) {
			switch ($uitype) {
				case 313:
					return new Twitter();
					break;
			}
		}
		return false;
	}

	/**
	 * Get all social media accounts.
	 *
	 * @param $socialMediaType
	 *
	 * @throws \App\Exceptions\AppException
	 *
	 * @return \App\SocialMedia\SocialMediaInterface|\Generator
	 */
	public static function getSocialMediaAccount($socialMediaType)
	{
		if (!\is_array($socialMediaType)) {
			$socialMediaType = [$socialMediaType];
		}
		$arrUitype = [];
		foreach ($socialMediaType as $val) {
			if (!isset(static::ALLOWED_UITYPE[$val])) {
				throw new \App\Exceptions\AppException('Invalid social media type');
			}
			$arrUitype[] = static::ALLOWED_UITYPE[$val];
		}
		$dataReader = (new \App\Db\Query())
			->select(['columnname', 'tablename', 'uitype'])
			->from('vtiger_field')
			->where(['uitype' => $arrUitype])
			->andWhere(['presence' => [0, 2]])
			->createCommand()
			->query();
		while (($row = $dataReader->read())) {
			$dataReaderAccounts = (new \App\Db\Query())
				->select([$row['columnname']])
				->from($row['tablename'])
				->andWhere(['not', [$row['columnname'] => null]])
				->andWhere(['not', [$row['columnname'] => '']])
				->createCommand()
				->query();
			while (($rowTwitter = $dataReaderAccounts->read())) {
				$twitterAccount = $rowTwitter[$row['columnname']];
				yield static::createObjectByUiType((int) $row['uitype']);
				//yield $twitterAccount;
			}
			$dataReaderAccounts->close();
		}
		$dataReader->close();
	}
}
