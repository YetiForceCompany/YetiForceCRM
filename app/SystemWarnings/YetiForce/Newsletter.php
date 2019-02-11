<?php

/**
 * Newsletter warning class file.
 *
 * @package   App
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Sławomir Kłos <s.klos@yetiforce.com>
 */

namespace App\SystemWarnings\YetiForce;

/**
 * Newsletter warning class.
 */
class Newsletter extends \App\SystemWarnings\Template
{
	/**
	 * @var string Warning title
	 */
	protected $title = 'LBL_NEWSLETTER';
	/**
	 * @var int Warning priority
	 */
	protected $priority = 8;
	/**
	 * @var bool Template flag
	 */
	protected $tpl = true;

	/**
	 * Checking if registration is correct and display modal with info if not.
	 */
	public function process()
	{
		if (static::emailProvided() && (\App\YetiForce\Register::verify(true) || \AppConfig::main('systemMode') === 'demo')) {
			$this->status = 1;
		} else {
			$this->status = 0;
		}
	}

	/**
	 * Check if email address is provided in company data.
	 *
	 * @param int|false $company
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
