<?php

/**
 * Updater system warning class file.
 *
 * @package   App
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace App\SystemWarnings\YetiForce;

/**
 * Updater warning class.
 */
class Updater extends \App\SystemWarnings\Template
{
	/**
	 * @var string Warning title
	 */
	protected $title = 'LBL_UPDATER';
	/**
	 * @var int Warning priority
	 */
	protected $priority = 8;

	/**
	 * Checking if registration is correct and display modal with info if not.
	 */
	public function process()
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
