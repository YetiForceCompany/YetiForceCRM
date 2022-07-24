<?php

/**
 * Roundcube library system warnings file.
 *
 * @package App
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

namespace App\SystemWarnings\SystemRequirements;

/**
 * Roundcube library system warnings class.
 */
class LibraryRoundcube extends \App\SystemWarnings\Template
{
	/** {@inheritdoc} */
	protected $title = 'LBL_LIBRARY_ROUNDCUBE';

	/** {@inheritdoc} */
	protected $priority = 4;

	/**
	 * Checking whether there is a library roundcube.
	 *
	 * @return void
	 */
	public function process(): void
	{
		$this->status = \Settings_ModuleManager_Library_Model::checkLibrary('roundcube') ? 0 : 1;
		if (0 === $this->status) {
			if (\App\Security\AdminAccess::isPermitted('ModuleManager')) {
				$this->link = 'index.php?module=ModuleManager&parent=Settings&view=List';
				$this->linkTitle = \App\Language::translate('BTN_DOWNLOAD_LIBRARY', 'Settings:SystemWarnings');
			}
			$this->description = \App\Language::translateArgs('LBL_MISSING_LIBRARY', 'Settings:SystemWarnings', \Settings_ModuleManager_Library_Model::$libraries['roundcube']['dir']);
		}
	}
}
