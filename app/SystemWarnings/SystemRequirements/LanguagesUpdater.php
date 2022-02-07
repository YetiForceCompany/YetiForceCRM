<?php

/**
 * Languages updater warning class file.
 *
 * @package App
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace App\SystemWarnings\SystemRequirements;

/**
 * Languages updater warning class.
 */
class LanguagesUpdater extends \App\SystemWarnings\Template
{
	/**
	 * @var string Warning title
	 */
	protected $title = 'LBL_LANGUAGES_UPDATER';
	/**
	 * @var int Warning priority
	 */
	protected $priority = 5;

	/**
	 * Checking if registration is correct and display modal with info if not.
	 *
	 * @return void
	 */
	public function process(): void
	{
		if (\App\Installer\Languages::getToInstall() && \App\Security\AdminAccess::isPermitted('LangManagement')) {
			$this->status = 0;
			$this->link = 'index.php?parent=Settings&module=LangManagement&view=Index';
			$this->linkTitle = \App\Language::translate('LBL_UPDATE', 'Settings:Base');
			$this->description = \App\Language::translate('LBL_LANGUAGES_UPDATER_DESC', 'Settings:SystemWarnings');
		} else {
			$this->status = 1;
		}
	}
}
