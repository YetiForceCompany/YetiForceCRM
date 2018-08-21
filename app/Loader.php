<?php

/**
 * Loader class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
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

	/**
	 * {@inheritdoc}
	 */
	public static function beginProfile($token, $category = 'application')
	{
		if (static::$logToProfile) {
			parent::beginProfile($token, $category);
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public static function endProfile($token, $category = 'application')
	{
		if (static::$logToProfile) {
			parent::endProfile($token, $category);
		}
	}
}

Yii::$container = new \yii\di\Container();
Yii::setLogger(Yii::createObject('App\Log'));
