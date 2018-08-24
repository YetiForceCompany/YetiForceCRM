<?php

namespace App\SystemWarnings\SystemRequirements;

/**
 * Conf report system php extensions warnings class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Sławomir Kłos <s.klos@yetiforce.com>
 */
class PhpExtensions extends \App\SystemWarnings\Template
{
	protected $title = 'LBL_CONFIG_PHP_EXTENSIONS';
	protected $priority = 7;

	/**
	 * Checking whether all the configuration parameters are correct.
	 */
	public function process()
	{
		$this->status = 1;
		$errors = \App\Utils\ConfReport::getAllErrors()['libraries'] ?? [];
		if (!empty($errors)) {
			$this->status = 0;
		}
		if ($this->status === 0) {
			$reference = \App\Utils\ConfReport::get('libraries');
			$errorsText = '<br><br><pre>';
			foreach ($errors as $key => $value) {
				$errorsText .= "\n{$key} (" . ($reference[$key]['mandatory'] ? \App\Language::translate('LBL_LIB_REQUIRED', 'Settings:SystemWarnings') : \App\Language::translate('LBL_LIB_OPTIONAL', 'Settings:SystemWarnings')) . ')';
			}
			$errorsText .= '</pre>';
			$this->link = 'index.php?parent=Settings&module=ConfReport&view=Index';
			$this->linkTitle = \App\Language::translate('LBL_CONFIG_REPORT_LINK', 'Settings:SystemWarnings');
			$this->description = \App\Language::translateArgs('LBL_CONFIG_LIB_DESC', 'Settings:SystemWarnings', '<a target="_blank" rel="noreferrer" href="' . \App\Language::translate('LBL_CONFIG_DOC_URL', 'Settings:SystemWarnings') . '"><u>' . \App\Language::translate('LBL_CONFIG_DOC_URL_LABEL', 'Settings:SystemWarnings') . '</u></a>') . $errorsText;
		}
	}
}
