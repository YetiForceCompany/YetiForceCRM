<?php

namespace App\SystemWarnings\Security;

/**
 * Security conf system warnings class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Sławomir Kłos <s.klos@yetiforce.com>
 */
class SecurityConf extends \App\SystemWarnings\Template
{
	protected $title = 'LBL_SECURITY_CONF';
	protected $priority = 7;

	/**
	 * Checking whether there is a security configuration issue.
	 */
	public function process()
	{
		$errors = \App\Utils\ConfReport::getAllErrors()['security'] ?? [];
		unset($errors['HTTPS'], $errors['public_html']);
		if (empty($errors)) {
			$this->status = 1;
		} else {
			$this->status = 0;
		}
		if ($this->status === 0) {
			$reference = \App\Utils\ConfReport::get('security');
			$errorsText = '<br><pre>';
			foreach ($errors as $key => $value) {
				$errorsText .= "\n{$key} = " . \yii\helpers\VarDumper::dumpAsString($value) . ' (' . \App\Language::translate('LBL_RECOMMENDED_VALUE', 'Settings:SystemWarnings') . ': \'' . $reference[$key]['recommended'] . '\')';
			}
			$errorsText .= '</pre>';
			$this->link = 'https://yetiforce.com/en/implementer/installation-updates/103-web-server-requirements.html';
			$this->linkTitle = \App\Language::translate('LBL_CONFIG_REPORT_LINK', 'Settings:SystemWarnings');
			$this->description = \App\Language::translateArgs('LBL_SECURITY_CONF_DESC', 'Settings:SystemWarnings', '<a target="_blank" rel="noreferrer" href="' . \App\Language::translate('LBL_CONFIG_DOC_URL', 'Settings:SystemWarnings') . '"><u>' . \App\Language::translate('LBL_CONFIG_DOC_URL_LABEL', 'Settings:SystemWarnings') . '</u></a>', $errorsText);
		}
	}
}
