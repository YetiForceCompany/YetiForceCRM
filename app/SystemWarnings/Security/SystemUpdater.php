<?php

/**
 * Updater system warning class file.
 *
 * @package App
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
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

	/** {@inheritdoc} */
	public function process(): void
	{
		if (\App\YetiForce\Updater::getToInstall()) {
			$this->status = 0;
			$this->description = \App\Language::translate('LBL_UPDATER_DESC', 'Settings:SystemWarnings') . '<br />';
			if (\App\Security\AdminAccess::isPermitted('Updates')) {
				$this->link = 'index.php?parent=Settings&module=Updates&view=Index';
				$this->linkTitle = \App\Language::translate('LBL_UPDATES_HISTORY');
			}
		} else {
			$this->status = 1;
		}
	}
}
