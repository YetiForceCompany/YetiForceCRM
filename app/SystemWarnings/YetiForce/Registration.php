<?php

/**
 * Registration system warning class file.
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
 * Registration warning class.
 */
class Registration extends \App\SystemWarnings\Template
{
	/** {@inheritdoc} */
	protected $title = 'LBL_REGISTRATION';

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
		if (\App\YetiForce\Register::verify(true) || 'demo' === \App\Config::main('systemMode')) {
			$this->status = 1;
		} else {
			$this->status = 0;
		}
	}
}
