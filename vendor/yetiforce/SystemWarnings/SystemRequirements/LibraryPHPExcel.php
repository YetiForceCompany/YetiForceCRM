<?php
namespace App\SystemWarnings\SystemRequirements;

/**
 * PHPExcel library system warnings class
 * @package YetiForce.SystemWarning
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 2.0 (licenses/License.html or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class LibraryPHPExcel extends \App\SystemWarnings\Template
{

	protected $title = 'LBL_LIBRARY_PHPEXCEL';
	protected $priority = 4;

	/**
	 * Checking whether there is a library PHPExcel
	 */
	public function process()
	{
		$this->status = \Settings_ModuleManager_Library_Model::checkLibrary('PHPExcel') ? 0 : 1;
		if ($this->status === 0) {
			$this->link = 'index.php?module=ModuleManager&parent=Settings&view=List';
			$this->linkTitle = \App\Language::translate('BTN_DOWNLOAD_LIBRARY', 'Settings:SystemWarnings');
			$this->description = \App\Language::translate('LBL_MISSING_LIBRARY', 'Settings:SystemWarnings', \Settings_ModuleManager_Library_Model::TEMP_DIR);
		}
	}
}
