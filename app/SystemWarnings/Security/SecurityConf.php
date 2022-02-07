<?php

/**
 * Security conf system warnings file.
 *
 * @package App
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Sławomir Kłos <s.klos@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace App\SystemWarnings\Security;

/**
 * Security conf system warnings class.
 */
class SecurityConf extends \App\SystemWarnings\Template
{
	/** {@inheritdoc} */
	protected $title = 'LBL_SECURITY_CONF';

	/** {@inheritdoc} */
	protected $priority = 7;

	/**
	 * Checking whether there is a security configuration issue.
	 *
	 * @return void
	 */
	public function process(): void
	{
		$this->status = 1;
		$errorsSecurity = \App\Utils\ConfReport::getErrors('security', true);
		unset($errorsSecurity['HTTPS']);
		$errorsText = '<br>';
		if (!empty($errorsSecurity)) {
			$errorsText .= '<h5>' . \App\Language::translate('LBL_SECURITY', 'Settings:SystemWarnings') . ':</h5><pre>';
			foreach ($errorsSecurity as $key => $value) {
				$errorsText .= PHP_EOL . "  {$key} = " . \yii\helpers\VarDumper::dumpAsString($value['val']) .
					' (' . \App\Language::translate('LBL_RECOMMENDED_VALUE', 'Settings:SystemWarnings') .
					": '" . ($value['recommended'] ?? '') . "')";
			}
			$errorsText .= '</pre><hr/>';
			$this->status = 0;
		}
		$errorsWritableFilesAndFolders = \App\Utils\ConfReport::getErrors('writableFilesAndFolders');
		if (!empty($errorsWritableFilesAndFolders)) {
			$errorsText .= '<h5>' . \App\Language::translate('LBL_NO_FILE_WRITE_RIGHTS', 'Settings:SystemWarnings') . ':</h5><pre>';
			foreach ($errorsWritableFilesAndFolders as $key => $value) {
				$errorsText .= PHP_EOL . "  {$key}";
			}
			$this->status = 0;
		}
		$errorsText .= '</pre>';
		if (!$this->status) {
			$this->link = 'https://yetiforce.com/en/knowledge-base/documentation/implementer-documentation/item/web-server-requirements';
			$this->linkTitle = \App\Language::translate('LBL_CONFIG_REPORT_LINK', 'Settings:SystemWarnings');
			$this->description = \App\Language::translateArgs(
				'LBL_SECURITY_CONF_DESC',
				'Settings:SystemWarnings',
				'<a target="_blank" rel="noreferrer noopener" href="https://yetiforce.com/en/knowledge-base/documentation/implementer-documentation/item/web-server-requirement"><u>' . \App\Language::translate('LBL_CONFIG_DOC_URL_LABEL', 'Settings:SystemWarnings') . '</u></a>'
			) . $errorsText;
		}
	}
}
