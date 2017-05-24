<?php
namespace App\SystemWarnings\SystemRequirements;

/**
 * Privilege File basic class
 * @package YetiForce.SystemWarning
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 2.0 (licenses/License.html or yetiforce.com)
 * @author Sławomir Kłos <s.klos@yetiforce.com>
 */
class ServerHttps extends \App\SystemWarnings\Template
{

	protected $status = 0;
	protected $title = 'LBL_SERVER_HTTPS';
	protected $priority = 7;

	/**
	 * Checking whether there is a https connection
	 */
	public function process()
	{
		if (isset($_SERVER['HTTPS']) && !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') {
			$this->status = 1;
		} else {
			$this->status = 0;
		}
		if ($this->status === 0) {
			$this->link = 'https://yetiforce.com/en/implementer/installation-updates/103-web-server-requirements.html';
			$this->linkTitle = vtranslate('BTN_CONFIGURE_HTTPS', 'Settings:SystemWarnings');
			$this->description = vtranslate('LBL_MISSING_HTTPS', 'Settings:SystemWarnings', \Settings_ModuleManager_Library_Model::TEMP_DIR);
		}
	}
}
