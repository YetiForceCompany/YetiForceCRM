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
	/**
	 * {@inheritdoc}
	 *
	 * @var string
	 */
	protected $title = 'LBL_SECURITY_CONF';
	/**
	 * {@inheritdoc}
	 *
	 * @var int
	 */
	protected $priority = 7;

	/**
	 * Checking whether there is a security configuration issue.
	 */
	public function process()
	{
		$this->status = 1;
		$errorsSecurity = \App\Utils\ConfReport::getErrors('security', true);
		unset($errorsSecurity['HTTPS']);
		$errorsText = '<br><pre>';
		if (!empty($errorsSecurity)) {
			$errorsText .= '<strong>' . \App\Language::translate('LBL_SECURITY', 'Settings:SystemWarnings') . ':</strong>';
			foreach ($errorsSecurity as $key => $value) {
				$errorsText .= \PHP_EOL . "  {$key} = " . \yii\helpers\VarDumper::dumpAsString($value['val']) .
					' (' . \App\Language::translate('LBL_RECOMMENDED_VALUE', 'Settings:SystemWarnings') .
					": '" . ($value['recommended'] ?? '') . "')";
			}
			$errorsText .= \PHP_EOL . \PHP_EOL;
			$this->status = 0;
		}
		$errorsWritableFilesAndFolders = \App\Utils\ConfReport::getErrors('writableFilesAndFolders');
		if (!empty($errorsWritableFilesAndFolders)) {
			$errorsText .= '<strong>' . \App\Language::translate('LBL_NO_FILE_WRITE_RIGHTS', 'Settings:SystemWarnings') . ':</strong>';
			foreach ($errorsWritableFilesAndFolders as $key => $value) {
				$errorsText .= \PHP_EOL . "  {$key}";
			}
			$this->status = 0;
		}
		$errorsText .= \PHP_EOL . \PHP_EOL;
		if (!$this->status) {
			$errorsText .= '</pre>';
			$this->link = 'https://yetiforce.com/en/knowledge-base/documentation/implementer-documentation/item/web-server-requirements';
			$this->linkTitle = \App\Language::translate('LBL_CONFIG_REPORT_LINK', 'Settings:SystemWarnings');
			$this->description = \App\Language::translateArgs(
				'LBL_SECURITY_CONF_DESC',
				'Settings:SystemWarnings',
				'<a target="_blank" rel="noreferrer noopener" href="' . \App\Language::translate('LBL_CONFIG_DOC_URL', 'Settings:SystemWarnings') . '"><u>' . \App\Language::translate('LBL_CONFIG_DOC_URL_LABEL', 'Settings:SystemWarnings') . '</u></a>',
				$errorsText
			);
		}
	}
}
