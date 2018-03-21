<?php

namespace App\SystemWarnings\SystemRequirements;

/**
 * Conf report system warnings class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class ConfReport extends \App\SystemWarnings\Template
{
	protected $title = 'LBL_CONFIG_REPORT';
	protected $priority = 7;

	/**
	 * Checking whether all the configuration parameters are correct.
	 */
	public function process()
	{
		$this->status = 1;
		$permissionsFiles = \Settings_ConfReport_Module_Model::getPermissionsFiles(true);
		if (!empty($permissionsFiles)) {
			$this->status = 0;
		}
		if ($this->status) {
			$library = \Settings_ConfReport_Module_Model::getLibrary();
			foreach ($library as $key => $value) {
				if ($value['status'] === 'LBL_NO' && $value['mandatory']) {
					$this->status = 0;
				}
			}
		}
		if ($this->status) {
			$directiveValues = \Settings_ConfReport_Module_Model::getStabilityConf();
			foreach ($directiveValues as $key => $value) {
				if (isset($value['status']) && $value['status']) {
					$this->status = 0;
				}
			}
		}
		if ($this->status === 0) {
			$this->link = 'index.php?parent=Settings&module=ConfReport&view=Index';
			$this->linkTitle = \App\Language::translate('LBL_CONFIG_REPORT_LINK', 'Settings:SystemWarnings');
			$this->description = \App\Language::translate('LBL_CONFIG_REPORT_DESC', 'Settings:SystemWarnings');
		}
	}
}
