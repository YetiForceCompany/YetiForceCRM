<?php namespace includes\SystemWarnings\SystemRequirements;

/**
 * Privilege File basic class
 * @package YetiForce.SystemWarnings
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class ConfReport extends \includes\SystemWarnings\Template
{

	protected $title = 'LBL_CONFIG_REPORT';
	protected $priority = 6;

	/**
	 * Checking whether all the configuration parameters are correct
	 */
	public function process()
	{
		$status = 0;
		$permissionsFiles = \Settings_ConfReport_Module_Model::getPermissionsFiles(true);
		if (!empty($permissionsFiles)) {
			$status = 0;
		}
		if ($status) {
			$library = \Settings_ConfReport_Module_Model::getConfigurationLibrary();
			foreach ($library as $key => $value) {
				if ($value['status'] == 'LBL_NO') {
					$status = 0;
				}
			}
		}
		if ($status) {
			$directiveValues = \Settings_ConfReport_Module_Model::getConfigurationValue();
			foreach ($directiveValues as $key => $value) {
				if (isset($value['status']) && $value['status']) {
					$status = 0;
				}
			}
		}
		if ($status) {
			$this->status = 1;
		} else {
			$this->status = 0;
			$this->link = 'index.php?parent=Settings&module=ConfReport&view=Index';
		}
	}
}
