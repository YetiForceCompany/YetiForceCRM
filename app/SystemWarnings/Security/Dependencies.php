<?php

namespace App\SystemWarnings\Security;

/**
 * Check for vulnerabilities in dependencies warnings class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Dependencies extends \App\SystemWarnings\Template
{
	protected $title = 'LBL_VULNERABILITIES_IN_DEPENDENCIES';
	protected $priority = 9;

	/**
	 * Checks if encryption is active.
	 */
	public function process()
	{
		if (!\App\RequestUtil::isNetConnection()) {
			$this->status = 1;
			return;
		}
		$vulnResults = (new \SensioLabs\Security\SecurityChecker())->check(ROOT_DIRECTORY);
		if ($vulnResults->count()) {
			$this->status = 0;
		} else {
			$this->status = 1;
		}
		if ($this->status === 0) {
			$vulns = \App\Json::decode((string)$vulnResults);
			$vulns = (\is_array($vulns) && !empty($vulns)) ? $vulns : [];
			$this->link = 'index.php?module=Vtiger&parent=Settings&view=Index&mode=security';
			$this->linkTitle = \App\Language::translate('Security', 'Settings:SystemWarnings');
			$this->description = \App\Language::translate('LBL_VULNERABILITIES_IN_DEPENDENCIES_DESC', 'Settings:SystemWarnings') . '<br />';
			foreach ($vulns as $name => $vulnerability) {
				$this->description .= "$name({$vulnerability['version']}):<br />";
				foreach ($vulnerability['advisories'] as $data) {
					$this->description .= "{$data['title']} {$data['cve']}<br />";
				}
				$this->description .= '<hr />';
			}
		}
	}
}
