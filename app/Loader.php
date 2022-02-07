<?php

/**
 * Loader class.
 *
 * @package App
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Yii extends \yii\BaseYii
{
	/**
	 * Profiler optimization.
	 *
	 * @var bool
	 */
	public static $logToProfile;

	/** {@inheritdoc} */
	public static function beginProfile($token, $category = 'application')
	{
		if (static::$logToProfile) {
			$categories = \Config\Debug::$LOG_PROFILE_CATEGORIES ?? [];
			if ($categories && !\in_array($category, $categories)) {
				return;
			}
			parent::beginProfile($token, $category);
		}
	}

	/** {@inheritdoc} */
	public static function endProfile($token, $category = 'application')
	{
		if (static::$logToProfile) {
			$categories = \Config\Debug::$LOG_PROFILE_CATEGORIES ?? [];
			if ($categories && !\in_array($category, $categories)) {
				return;
			}
			parent::endProfile($token, $category);
		}
	}
}

Yii::$container = new \yii\di\Container();
Yii::setLogger(Yii::createObject('App\Log'));
