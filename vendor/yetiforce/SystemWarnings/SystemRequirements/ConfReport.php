<?php
namespace App\SystemWarnings\SystemRequirements;

/**
 * Privilege File basic class
 * @package YetiForce.SystemWarnings
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class ConfReport extends \App\SystemWarnings\Template
{

	protected $status = 2;
	protected $title = 'LBL_CONFIG_REPORT';
	protected $priority = 7;

	/**
	 * Checking whether all the configuration parameters are correct
	 */
	public function process()
	{
		$status = 2;
		$permissionsFiles = \Settings_ConfReport_Module_Model::getPermissionsFiles(true);
		if (!empty($permissionsFiles)) {
			$status = 2;
		}
		if ($status) {
			$library = \Settings_ConfReport_Module_Model::getConfigurationLibrary();
			foreach ($library as $key => $value) {
				if ($value['status'] === 'LBL_NO') {
					$status = 2;
				}
			}
		}
		if ($status) {
			$directiveValues = \Settings_ConfReport_Module_Model::getConfigurationValue();
			foreach ($directiveValues as $key => $value) {
				if (isset($value['status']) && $value['status']) {
					$status = 2;
				}
			}
		}
		if ($status) {
			$this->status = 1;
		} else {
			$this->status = 0;
			$this->link = 'index.php?parent=Settings&module=ConfReport&view=Index';
			$this->linkTitle = \App\Language::translate('LBL_CONFIG_REPORT_LINK', 'Settings:SystemWarnings');
			$this->description = \App\Language::translate('LBL_CONFIG_REPORT_DESC', 'Settings:SystemWarnings');
		}
	}
}
