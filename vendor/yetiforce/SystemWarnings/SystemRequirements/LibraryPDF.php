<?php
namespace App\SystemWarnings\SystemRequirements;

/**
 * Privilege File basic class
 * @package YetiForce.SystemWarnings
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class LibraryPDF extends \App\SystemWarnings\Template
{

	protected $title = 'LBL_LIBRARY_PDF';
	protected $priority = 4;

	/**
	 * Checking whether there is a library mPDF
	 */
	public function process()
	{
		$this->status = \Settings_ModuleManager_Library_Model::checkLibrary('mPDF') ? 0 : 1;
		if ($this->status === 0) {
			$this->link = 'index.php?module=ModuleManager&parent=Settings&view=List';
			$this->linkTitle = vtranslate('BTN_DOWNLOAD_LIBRARY', 'Settings:SystemWarnings');
			$this->description = vtranslate('LBL_MISSING_LIBRARY', 'Settings:SystemWarnings', \Settings_ModuleManager_Library_Model::TEMP_DIR);
		}
	}
}
