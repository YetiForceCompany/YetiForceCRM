<?php

namespace App\SystemWarnings\SystemRequirements;

/**
 * Roundcube library system warnings class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class LibraryRoundcube extends \App\SystemWarnings\Template
{
	protected $title = 'LBL_LIBRARY_ROUNDCUBE';
	protected $priority = 4;

	/**
	 * Checking whether there is a library roundcube.
	 */
	public function process()
	{
		$this->status = \Settings_ModuleManager_Library_Model::checkLibrary('roundcube') ? 0 : 1;
		if ($this->status === 0) {
			$this->link = 'index.php?module=ModuleManager&parent=Settings&view=List';
			$this->linkTitle = \App\Language::translate('BTN_DOWNLOAD_LIBRARY', 'Settings:SystemWarnings');
			$this->description = \App\Language::translateArgs('LBL_MISSING_LIBRARY', 'Settings:SystemWarnings', \Settings_ModuleManager_Library_Model::$libraries['roundcube']['dir']);
		}
	}
}
