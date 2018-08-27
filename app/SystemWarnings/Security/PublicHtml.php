<?php

namespace App\SystemWarnings\Security;

/**
 * Public html usage system warnings class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Sławomir Kłos <s.klos@yetiforce.com>
 */
class PublicHtml extends \App\SystemWarnings\Template
{
	protected $title = 'LBL_PUBLIC_ACCESS';
	protected $priority = 7;

	/**
	 * Checking whether there is a public_html directory in use.
	 */
	public function process()
	{
		$errors = \App\Utils\ConfReport::getAllErrors()['publicDirectoryAccess'] ?? [];
		if (empty($errors)) {
			$this->status = 1;
		} else {
			$this->status = 0;
		}
		if ($this->status === 0) {
			$errorsText = '<br><pre>';
			foreach ($errors as $key => $value) {
				$errorsText .= "\n{$key}";
			}
			$errorsText .= '</pre>';
			$this->link = 'https://yetiforce.com/en/implementer/installation-updates/103-web-server-requirements.html';
			$this->linkTitle = \App\Language::translate('LBL_CONFIG_REPORT_LINK', 'Settings:SystemWarnings');
			$this->description = \App\Language::translateArgs('LBL_PUBLIC_ACCESS_DESC', 'Settings:SystemWarnings', '<a target="_blank" rel="noreferrer" href="' . \App\Language::translate('LBL_CONFIG_DOC_URL', 'Settings:SystemWarnings') . '"><u>' . \App\Language::translate('LBL_CONFIG_DOC_URL_LABEL', 'Settings:SystemWarnings') . '</u></a>', $errorsText);
		}
	}
}
