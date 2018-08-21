<?php

namespace App\SystemWarnings\Security;

/**
 * Encryption warnings class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Tomasz Kur <t.kur@yetiforce.com>
 */
class Encryption extends \App\SystemWarnings\Template
{
	protected $title = 'LBL_MECHANISM_ENCRYPTION';
	protected $priority = 9;

	/**
	 * Checks if encryption is active.
	 */
	public function process()
	{
		if (\App\Encryption::getInstance()->isActive()) {
			$this->status = 1;
		} else {
			$this->status = 0;
		}
		if ($this->status === 0) {
			$this->link = 'index.php?module=Password&parent=Settings&view=Encryption';
			$this->linkTitle = \App\Language::translate('BTN_CONFIGURE_ENCRYPTION', 'Settings:SystemWarnings');
			$this->description = \App\Language::translate('LBL_CONFIGURE_ENCRYPTION_DESCRIPTION', 'Settings:SystemWarnings');
		}
	}
}
