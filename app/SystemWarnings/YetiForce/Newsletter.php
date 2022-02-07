<?php

/**
 * Newsletter warning class file.
 *
 * @package App
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Sławomir Kłos <s.klos@yetiforce.com>
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace App\SystemWarnings\YetiForce;

/**
 * Newsletter warning class.
 */
class Newsletter extends \App\SystemWarnings\Template
{
	/** {@inheritdoc} */
	protected $title = 'LBL_NEWSLETTER';

	/** {@inheritdoc} */
	protected $priority = 8;

	/** {@inheritdoc} */
	protected $tpl = true;

	/**
	 * Checking if registration is correct and display modal with info if not.
	 *
	 * @return void
	 */
	public function process(): void
	{
		if (static::emailProvided() && (\App\YetiForce\Register::verify(true) || 'demo' === \App\Config::main('systemMode'))) {
			$this->status = 1;
		} else {
			$this->status = 0;
		}
	}

	/**
	 * Check if email address is provided in company data.
	 *
	 * @param false|int $company
	 *
	 * @return bool
	 */
	public static function emailProvided($company = false)
	{
		$query = (new \App\Db\Query())->from('s_#__companies')->where(['and',
			['not', ['email' => null]],
			['<>', 'email', ''],
		]);
		if ($company) {
			$query->andWhere(['id' => $company]);
		}
		return $query->exists();
	}
}
