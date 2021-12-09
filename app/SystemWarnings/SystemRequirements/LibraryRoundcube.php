<?php

/**
 * Roundcube library system warnings file.
 *
 * @package App
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 4.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
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
			$this->link = 'index.php?module=ModuleManager&parent=Settings&view=List';
			$this->linkTitle = \App\Language::translate('BTN_DOWNLOAD_LIBRARY', 'Settings:SystemWarnings');
			$this->description = \App\Language::translateArgs('LBL_MISSING_LIBRARY', 'Settings:SystemWarnings', \Settings_ModuleManager_Library_Model::$libraries['roundcube']['dir']);
		}
	}
}
