<?php

namespace App\SystemWarnings\SystemRequirements;

/**
 * Https system warnings class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Sławomir Kłos <s.klos@yetiforce.com>
 */
class ServerHttps extends \App\SystemWarnings\Template
{
	protected $title = 'LBL_SERVER_HTTPS';
	protected $priority = 7;

	/**
	 * Checking whether there is a https connection.
	 */
	public function process()
	{
		if (\App\Config::$requestMode !== 'WebUI') {
			$this->status = 1;

			return;
		}
		if (\App\RequestUtil::getBrowserInfo()->https) {
			$this->status = 1;
		} else {
			$this->status = 0;
		}
		if ($this->status === 0) {
			$requestUrl = (\App\RequestUtil::getBrowserInfo()->https ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
			$this->link = $requestUrl . '?parent=Settings&module=ConfReport&view=Index';
			$this->linkTitle = \App\Language::translate('BTN_CONFIGURE_HTTPS', 'Settings:SystemWarnings');
			$this->description = \App\Language::translate('LBL_MISSING_HTTPS', 'Settings:SystemWarnings', \Settings_ModuleManager_Library_Model::TEMP_DIR);
		}
	}
}
