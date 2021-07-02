<?php

namespace App\SystemWarnings\Security;

/**
 * Check for vulnerabilities in dependencies warnings class.
 *
 * @package App
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 4.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Dependencies extends \App\SystemWarnings\Template
{
	/**
	 * Title.
	 *
	 * @var string
	 */
	protected $title = 'LBL_VULNERABILITIES_IN_DEPENDENCIES';
	/**
	 * Priority.
	 *
	 * @var int
	 */
	protected $priority = 9;

	/**
	 * Checks if encryption is active.
	 */
	public function process()
	{
		try {
			$checker = (new \App\Security\Dependency())->securityChecker();
			$this->status = $checker ? 0 : 1;
		} catch (\Throwable $e) {
			$this->status = 1;
		}
		if (0 === $this->status) {
			if (\App\Security\AdminAccess::isPermitted('Dependencies')) {
				$this->link = 'index.php?module=Dependencies&parent=Settings&view=Vulnerabilities';
				$this->linkTitle = \App\Language::translate('Security', 'Settings:SystemWarnings');
			}
			$this->description = \App\Language::translate('LBL_VULNERABILITIES_IN_DEPENDENCIES_DESC', 'Settings:SystemWarnings') . '<br />';
			foreach ($checker as $type => $vulnerabilities) {
				$type = strtoupper($type);
				$this->description .= '<h3><u>' . \App\Language::translate("LBL_SECURITY_{$type}", 'Settings:Dependencies') . ':</u></h3><br />';
				foreach ($vulnerabilities as $name => $vulnerability) {
					$this->description .= "<h4>$name ({$vulnerability['version']}):</h4><br />";
					$this->description .= '<ul>';
					foreach ($vulnerability['advisories'] as $data) {
						$this->description .= "<li><h5><a rel=\"noreferrer noopener\" target=\"_blank\" href=\"{$data['link']}\">{$data['cve']}</a></h5> {$data['title']}</li>";
					}
					$this->description .= '</ul><hr />';
				}
			}
		}
	}
}
