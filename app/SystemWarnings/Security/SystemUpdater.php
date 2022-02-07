<?php

/**
 * Updater system warning class file.
 *
 * @package App
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 4.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace App\SystemWarnings\Security;

/**
 * Updater warning class.
 */
class SystemUpdater extends \App\SystemWarnings\Template
{
	/** {@inheritdoc} */
	protected $title = 'LBL_SYSTEM_UPDATER';

	/** {@inheritdoc} */
	protected $priority = 8;

	/**
	 * Checking if registration is correct and display modal with info if not.
	 *
	 * @return void
	 */
	public function process(): void
	{
		if (\App\YetiForce\Updater::getToInstall()) {
			$this->status = 0;
			$this->link = 'index.php?parent=Settings&module=Updates&view=Index';
			$this->linkTitle = \App\Language::translate('LBL_UPDATES_HISTORY');
			$this->description = \App\Language::translate('LBL_UPDATER_DESC', 'Settings:SystemWarnings') . '<br />';
		} else {
			$this->status = 1;
		}
	}
}
