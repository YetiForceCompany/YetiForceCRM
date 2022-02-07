<?php

/**
 * Conf report system stability warnings file.
 *
 * @package App
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Sławomir Kłos <s.klos@yetiforce.com>
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace App\SystemWarnings\SystemRequirements;

/**
 * Conf report system stability warnings class.
 */
class Configuration extends \App\SystemWarnings\Template
{
	/** {@inheritdoc} */
	protected $title = 'LBL_CONFIG_SERVER';

	/** {@inheritdoc} */
	protected $priority = 7;

	/**
	 * Checking whether all the configuration parameters are correct.
	 *
	 * @return void
	 */
	public function process(): void
	{
		\App\Utils\ConfReport::saveEnv();
		$this->status = 1;
		$errorsText = '<br>';
		$errorsStability = \App\Utils\ConfReport::getErrors('stability', true);
		if (!empty($errorsStability)) {
			$errorsText .= '<h5>' . \App\Language::translate('LBL_STABILITY', 'Settings:SystemWarnings') . ':</h5><pre>';
			foreach ($errorsStability as $key => $value) {
				$errorsText .= PHP_EOL . "  {$key} = " . \yii\helpers\VarDumper::dumpAsString($value['val']) .
					' (' . \App\Language::translate('LBL_RECOMMENDED_VALUE', 'Settings:SystemWarnings') .
					": '" . ($value['recommended'] ?? '') . "')";
			}
			$errorsText .= '</pre><hr/>';
			$this->status = 0;
		}
		$errorsDatabase = \App\Utils\ConfReport::getErrors('database', true);
		if (!empty($errorsDatabase)) {
			$errorsText .= '<h5>' . \App\Language::translate('LBL_DATABASE', 'Settings:SystemWarnings') . ':</h5><pre>';
			foreach ($errorsDatabase as $key => $value) {
				$errorsText .= PHP_EOL . "  {$key} = " . \yii\helpers\VarDumper::dumpAsString($value['val']) .
					' (' . \App\Language::translate('LBL_RECOMMENDED_VALUE', 'Settings:SystemWarnings') .
					": '" . ($value['recommended'] ?? '') . "')";
			}
			$errorsText .= '</pre><hr/>';
			$this->status = 0;
		}
		$errorsLibraries = \App\Utils\ConfReport::getErrors('libraries', true);
		if (!empty($errorsLibraries)) {
			$noMandatoryLib = false;
			foreach ($errorsLibraries as $key => $value) {
				if (isset($value['mandatory'])) {
					if (!$noMandatoryLib) {
						$errorsText .= '<h4>' . \App\Language::translate('LBL_PHPEXT', 'Settings:SystemWarnings') . ':</h4>';
						$noMandatoryLib = true;
					}
					$errorsText .= PHP_EOL . "<pre>{$key} (" .
						($value['mandatory'] ? \App\Language::translate('LBL_LIB_REQUIRED', 'Settings:SystemWarnings') : \App\Language::translate('LBL_LIB_OPTIONAL', 'Settings:SystemWarnings')) .
						')</pre>';
				}
			}
			if ($noMandatoryLib) {
				$errorsText .= '<hr/>';
				$this->status = 0;
			}
		}
		$errorsPerformance = \App\Utils\ConfReport::getErrors('performance', true);
		if (!empty($errorsPerformance)) {
			$performance = '';
			foreach ($errorsPerformance as $key => $value) {
				if (isset($value['recommended'])) {
					$performance .= PHP_EOL . "  {$key} = " . \yii\helpers\VarDumper::dumpAsString($value['val']) .
						' (' . \App\Language::translate('LBL_RECOMMENDED_VALUE', 'Settings:SystemWarnings') .
						": '" . ($value['recommended'] ?? '') . "')";
				}
			}
			if ($performance) {
				$errorsText .= '<h5>' . \App\Language::translate('LBL_PERFORMANCE', 'Settings:SystemWarnings') . ':</h5>';
				$errorsText .= "<pre>$performance</pre>";
				$this->status = 0;
			}
		}
		$errorsText .= '</pre>';
		if (!$this->status) {
			if (\App\Security\AdminAccess::isPermitted('ConfReport')) {
				$this->link = 'index.php?parent=Settings&module=ConfReport&view=Index';
				$this->linkTitle = \App\Language::translate('LBL_CONFIG_REPORT_LINK', 'Settings:SystemWarnings');
			}
			$this->description = \App\Language::translateArgs(
					'LBL_CONFIG_SERVER_DESC',
					'Settings:SystemWarnings',
					'<a target="_blank" rel="noreferrer noopener" href="https://yetiforce.com/en/knowledge-base/documentation/implementer-documentation/item/web-server-requirement"><u>' . \App\Language::translate('LBL_CONFIG_DOC_URL_LABEL', 'Settings:SystemWarnings') . '</u></a>'
				) . $errorsText;
		}
	}
}
