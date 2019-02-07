<?php

namespace App\SystemWarnings\SystemRequirements;

/**
 * Conf report system stability warnings class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Sławomir Kłos <s.klos@yetiforce.com>
 */
class Configuration extends \App\SystemWarnings\Template
{
	/**
	 * {@inheritdoc}
	 *
	 * @var string
	 */
	protected $title = 'LBL_CONFIG_SERVER';
	/**
	 * {@inheritdoc}
	 *
	 * @var int
	 */
	protected $priority = 7;

	/**
	 * Checking whether all the configuration parameters are correct.
	 */
	public function process()
	{
		$this->status = 1;
		$errorsText = '<br><pre>';
		$errorsStability = \App\Utils\ConfReport::getErrors('stability', true);
		if (!empty($errorsStability)) {
			$errorsText .= '<strong>' . \App\Language::translate('LBL_STABILITY', 'Settings:SystemWarnings') . ':</strong>';
			foreach ($errorsStability as $key => $value) {
				$errorsText .= PHP_EOL . "  {$key} = " . \yii\helpers\VarDumper::dumpAsString($value['val']) .
					' (' . \App\Language::translate('LBL_RECOMMENDED_VALUE', 'Settings:SystemWarnings') .
					": '" . ($value['recommended'] ?? '') . "')";
			}
			$errorsText .= PHP_EOL . PHP_EOL;
			$this->status = 0;
		}
		$errorsDatabase = \App\Utils\ConfReport::getErrors('database', true);
		if (!empty($errorsDatabase)) {
			$errorsText .= '<strong>' . \App\Language::translate('LBL_DATABASE', 'Settings:SystemWarnings') . ':</strong>';
			foreach ($errorsDatabase as $key => $value) {
				$errorsText .= PHP_EOL . "  {$key} = " . \yii\helpers\VarDumper::dumpAsString($value['val']) .
					' (' . \App\Language::translate('LBL_RECOMMENDED_VALUE', 'Settings:SystemWarnings') .
					": '" . ($value['recommended'] ?? '') . "')";
			}
			$errorsText .= PHP_EOL . PHP_EOL;
			$this->status = 0;
		}
		$errorsLibraries = \App\Utils\ConfReport::getErrors('libraries', true);
		if (!empty($errorsLibraries)) {
			$noMandatoryLib = false;
			foreach ($errorsLibraries as $key => $value) {
				if (!empty($value['mandatory'])) {
					if (!$noMandatoryLib) {
						$errorsText .= '<strong>' . \App\Language::translate('LBL_PHPEXT', 'Settings:SystemWarnings') . ':</strong>';
						$noMandatoryLib = true;
					}
					$errorsText .= PHP_EOL . "{$key} (" .
						($value['mandatory'] ? \App\Language::translate('LBL_LIB_REQUIRED', 'Settings:SystemWarnings') : \App\Language::translate('LBL_LIB_OPTIONAL', 'Settings:SystemWarnings')) .
						')';
				}
			}
			if ($noMandatoryLib) {
				$errorsText .= PHP_EOL . PHP_EOL;
				$this->status = 0;
			}
		}
		$errorsPerformance = \App\Utils\ConfReport::getErrors('performance', true);
		if (!empty($errorsPerformance)) {
			$errorsText .= '<strong>' . \App\Language::translate('LBL_PERFORMANCE', 'Settings:SystemWarnings') . ':</strong>';
			foreach ($errorsPerformance as $key => $value) {
				if (!empty($value['recommended'])) {
					$errorsText .= PHP_EOL . "  {$key} = " . \yii\helpers\VarDumper::dumpAsString($value['val']) .
						' (' . \App\Language::translate('LBL_RECOMMENDED_VALUE', 'Settings:SystemWarnings') .
						": '" . ($value['recommended'] ?? '') . "')";
				}
			}
			$errorsText .= PHP_EOL . PHP_EOL;
			$this->status = 0;
		}
		$errorsText .= '</pre>';
		if (!$this->status) {
			$this->link = 'index.php?parent=Settings&module=ConfReport&view=Index';
			$this->linkTitle = \App\Language::translate('LBL_CONFIG_REPORT_LINK', 'Settings:SystemWarnings');
			$this->description = \App\Language::translateArgs(
					'LBL_CONFIG_SERVER_DESC',
					'Settings:SystemWarnings',
					'<a target="_blank" rel="noreferrer noopener" href="' . \App\Language::translate('LBL_CONFIG_DOC_URL', 'Settings:SystemWarnings') .
					'"><u>' . \App\Language::translate('LBL_CONFIG_DOC_URL_LABEL', 'Settings:SystemWarnings') . '</u></a>'
				) . $errorsText;
		}
	}
}
